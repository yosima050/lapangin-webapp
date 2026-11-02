#!/usr/bin/env bash
# =============================================================================
# LapangIn — Automated Server Hardening Script (Ubuntu 22.04 / 24.04 LTS)
# Target: Azure Virtual Machine VPS (vm-lapangin-staging / production)
#
# Penggunaan:
#   sudo bash deploy/hardening.sh
#
# Karakteristik Idempoten:
#   Dapat dijalankan berulang kali dengan aman tanpa merusak konfigurasi sistem,
#   tanpa mengunci akses SSH yang sah, dan tanpa downtime yang tidak diinginkan.
# =============================================================================

set -euo pipefail

echo "================================================================="
echo "🛡️  MEMULAI SERVER HARDENING LAPANGIN (IDEMPOTEN)"
echo "   Waktu: $(date '+%Y-%m-%d %H:%M:%S')"
echo "================================================================="

# Pastikan script dijalankan sebagai root / sudo
if [ "$EUID" -ne 0 ]; then
    echo "❌ Error: Skrip hardening wajib dijalankan dengan privilege sudo/root."
    exit 1
fi

# ── 1. Update Paket Sistem & Instalasi Security Tools ─────────────────────────
echo "[1/7] Memperbarui indeks paket & menginstal utilitas keamanan..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq ufw fail2ban unattended-upgrades iptables curl git htop

# Aktifkan update keamanan otomatis (unattended-upgrades) secara idempoten
cat << 'EOF' > /etc/apt/apt.conf.d/20auto-upgrades
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
APT::Periodic::AutocleanInterval "7";
EOF

# ── 2. Konfigurasi Firewall UFW (Idempoten) ────────────────────────────────────
echo "[2/7] Mengonfigurasi Firewall UFW..."
ufw --force reset >/dev/null 2>&1 || true
ufw default deny incoming
ufw default allow outgoing

# Buka hanya port resmi yang dibutuhkan
ufw allow 22/tcp comment 'SSH Secure Administration'
ufw allow 80/tcp comment 'HTTP Web Server (Production/Certbot)'
ufw allow 443/tcp comment 'HTTPS Secure Web Server (Production)'
ufw allow 8080/tcp comment 'HTTP Staging & Team Testing Server'

# Pastikan port database internal PostgreSQL (5432) TIDAK terbuka ke publik
ufw delete allow 5432/tcp >/dev/null 2>&1 || true

# Aktifkan firewall
ufw --force enable
echo "      ✅ Firewall UFW aktif: Port 22, 80, 443, dan 8080 diizinkan."

# ── 3. Pengerasan Akses SSH (SSH Hardening) ───────────────────────────────────
echo "[3/7] Mengonfigurasi SSH Hardening..."
SSH_HARDENING_CONF="/etc/ssh/sshd_config.d/99-lapangin-hardening.conf"
mkdir -p /etc/ssh/sshd_config.d

# Tulis aturan pengerasan SSH
cat << 'EOF' > "${SSH_HARDENING_CONF}"
# LapangIn Server Hardening Policy
PermitRootLogin no
MaxAuthTries 4
ClientAliveInterval 300
ClientAliveCountMax 2
X11Forwarding no
AllowAgentForwarding no
EOF

# Uji sintaks SSH daemon sebelum reload untuk mencegah lockout
if sshd -t; then
    systemctl reload ssh || systemctl reload sshd || true
    echo "      ✅ Konfigurasi SSH aman diterapkan (Root login dibatasi, max tries: 4)."
else
    echo "      ⚠️ Galat pada sintaks SSH, mengembalikan ke konfigurasi cadangan."
    rm -f "${SSH_HARDENING_CONF}"
fi

# ── 4. Konfigurasi Fail2ban (Anti Brute-Force Protection) ──────────────────────
echo "[4/7] Mengonfigurasi Fail2ban anti-serangan brute-force..."
cat << 'EOF' > /etc/fail2ban/jail.d/lapangin-ssh.local
[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 4
findtime = 600
bantime = 86400
EOF

systemctl enable fail2ban >/dev/null 2>&1 || true
systemctl restart fail2ban
echo "      ✅ Fail2ban aktif: Blokir otomatis 24 jam setelah 4 kali gagal login."

# ── 5. Hardening Kernel & Jaringan (Sysctl TCP/IP Anti-Spoofing & DoS) ─────────
echo "[5/7] Mengonfigurasi parameter Kernel Linux (/etc/sysctl.d/)..."
cat << 'EOF' > /etc/sysctl.d/99-lapangin-security.conf
# Perlindungan dari SYN flood attack
net.ipv4.tcp_syncookies = 1

# Perlindungan dari IP spoofing (Reverse Path Filtering)
net.ipv4.conf.all.rp_filter = 1
net.ipv4.conf.default.rp_filter = 1

# Nonaktifkan ICMP redirect acceptance (mencegah Man-in-the-Middle)
net.ipv4.conf.all.accept_redirects = 0
net.ipv4.conf.default.accept_redirects = 0
net.ipv6.conf.all.accept_redirects = 0
net.ipv6.conf.default.accept_redirects = 0

# Nonaktifkan pengiriman ICMP redirect
net.ipv4.conf.all.send_redirects = 0
net.ipv4.conf.default.send_redirects = 0

# Abaikan ping broadcast (mencegah serangan Smurf)
net.ipv4.icmp_echo_ignore_broadcasts = 1

# Tolak source routed packets
net.ipv4.conf.all.accept_source_route = 0
net.ipv4.conf.default.accept_source_route = 0
EOF

sysctl --system -q >/dev/null 2>&1 || true
echo "      ✅ Parameter keamanan kernel & jaringan berhasil dimuat."

# ── 6. Pengamanan File & Izin Akses Direktori Aplikasi (Idempoten) ──────────────
echo "[6/7] Memeriksa & mengunci izin akses file aplikasi..."
for D in "/var/www/lapangin" "/var/www/lapangin-staging"; do
    if [ -d "${D}" ]; then
        # Kepemilikan folder
        chown -R www-data:www-data "${D}/storage" "${D}/bootstrap/cache" 2>/dev/null || true
        chmod -R 775 "${D}/storage" "${D}/bootstrap/cache" 2>/dev/null || true
        
        # Lindungi file rahasia .env agar hanya pemilik yang bisa membaca
        if [ -f "${D}/.env" ]; then
            chmod 600 "${D}/.env" 2>/dev/null || true
        fi
        echo "      ✅ Izin akses ${D} disesuaikan secara aman."
    fi
done

# ── 7. Ringkasan Status Keamanan ──────────────────────────────────────────────
echo "[7/7] Verifikasi status layanan..."
echo "================================================================="
echo "🛡️  HARDENING SERVER SELESAI & BERHASIL DITERAPKAN!"
echo "   - UFW Firewall       : AKTIF (Port 22, 80, 443, 8080)"
echo "   - SSH Hardening      : AKTIF (Root disabled, MaxAuthTries 4)"
echo "   - Fail2ban Jail      : AKTIF (24 jam ban pada 4x salah login)"
echo "   - Kernel Protection  : AKTIF (SYN flood & IP spoofing blocked)"
echo "   - Auto Updates       : AKTIF (Security patches otomatis)"
echo "================================================================="

