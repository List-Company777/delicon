@switch($type)
    @case('instagram')
        <svg width="16" height="16" class="shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" fill="#e1306c"/>
            <circle cx="12" cy="12" r="4" fill="none" stroke="white" stroke-width="1.8"/>
            <circle cx="17.5" cy="6.5" r="1.2" fill="white"/>
        </svg>
        @break
    @case('tiktok')
        <svg width="16" height="16" class="shrink-0" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="color:#000">
            <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.84a8.2 8.2 0 004.79 1.53V6.91a4.85 4.85 0 01-1.02-.22z"/>
        </svg>
        @break
    @case('x')
        <svg width="16" height="16" class="shrink-0" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="color:#000">
            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L2.02 2.25h6.638l4.244 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
        </svg>
        @break
    @case('line')
        <svg width="16" height="16" class="shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="24" height="24" rx="5" fill="#06C755"/>
            <path d="M12 4.5C7.86 4.5 4.5 7.28 4.5 10.7c0 2.17 1.36 4.08 3.42 5.24-.15.54-.56 1.96-.64 2.26-.1.38.14.37.29.27.12-.08 1.9-1.27 2.67-1.79.56.08 1.14.12 1.74.12 4.14 0 7.5-2.78 7.5-6.2C19.5 7.28 16.14 4.5 12 4.5z" fill="white"/>
            <path d="M9.2 12.1H7.8v-3H9.2v3zm3.6 0h-1.3l-1.4-2v2H8.8v-3h1.3l1.4 2v-2h1.3v3zm1.4 0v-3h1.4v3H14.2zm3.4 0H16v-3h1.6v.8H17v.4h.6v.8H17v.2h.6v.8z" fill="#06C755"/>
        </svg>
        @break
    @case('youtube')
        <svg width="16" height="16" class="shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="24" height="24" rx="4" fill="#FF0000"/>
            <path d="M19.5 8.5s-.2-1.3-.8-1.9c-.7-.8-1.6-.8-2-.8C14.7 5.7 12 5.7 12 5.7s-2.7 0-4.7.1c-.4 0-1.3 0-2 .8-.6.6-.8 1.9-.8 1.9S4.3 9.9 4.3 11.3v1.3c0 1.4.2 2.8.2 2.8s.2 1.3.8 1.9c.7.8 1.7.7 2.2.8C8.8 18.2 12 18.3 12 18.3s2.7 0 4.7-.1c.4 0 1.3 0 2-.8.6-.6.8-1.9.8-1.9s.2-1.4.2-2.8v-1.3c0-1.4-.2-2.8-.2-2.9zm-11.1 5.7V9.8l5.4 2.2-5.4 2.2z" fill="white"/>
        </svg>
        @break
    @case('website')
        <svg width="16" height="16" class="shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
        </svg>
        @break
    @default
        <svg width="16" height="16" class="shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
        </svg>
@endswitch
