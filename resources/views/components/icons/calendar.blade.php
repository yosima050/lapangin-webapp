@props(['class' => 'w-5 h-5'])

<svg {{ $attributes->merge(['class' => $class]) }}
     viewBox="0 0 24 24"
     fill="none"
     xmlns="http://www.w3.org/2000/svg"
     aria-hidden="true">
    {{-- Ring Spiral Lengkung Atas --}}
    <path d="M7.5 6.8V4.9C7.5 3.7 8.4 2.8 9.5 2.8C10.5 2.8 11.3 3.5 11.5 4.4"
          stroke="currentColor"
          stroke-width="1.9"
          stroke-linecap="round"
          stroke-linejoin="round" />
    <path d="M14.2 6.8V4.9C14.2 3.7 15.1 2.8 16.2 2.8C17.2 2.8 18 3.5 18.2 4.4"
          stroke="currentColor"
          stroke-width="1.9"
          stroke-linecap="round"
          stroke-linejoin="round" />
    {{-- Halaman Depan Kalender --}}
    <rect x="3" y="6.5" width="15" height="14.5" rx="3"
          stroke="currentColor"
          stroke-width="1.9"
          stroke-linejoin="round" />
    {{-- Sisi Kanan Berlapis (Efek 3D Kalender Meja) --}}
    <path d="M18 7.5C19.7 7.8 21 9.1 21 10.9V17.9C21 19.6 19.6 21 17.9 21H16.5"
          stroke="currentColor"
          stroke-width="1.9"
          stroke-linecap="round"
          stroke-linejoin="round" />
    {{-- Kisi Garis Tengah Membulat --}}
    <path d="M6.3 11H14.7M6.3 14.3H14.7M6.3 17.6H14.7M9 10.1V18.5M12 10.1V18.5"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round" />
</svg>
