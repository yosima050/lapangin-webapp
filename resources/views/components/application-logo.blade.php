<div {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    <!-- Logo Revisi Resmi LapangIn -->
    <img src="{{ asset('images/Logo_revisi.png') }}"
         alt="LapangIn Logo"
         class="h-10 sm:h-11 w-auto object-contain shrink-0"
         onerror="this.onerror=null; this.src='{{ asset('images/02_Icon_App.png') }}';">
</div>
