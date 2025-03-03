<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[#5865F2] dark:bg-[#5865F2] border border-transparent rounded-lg font-semibold text-sm text-[#FFFFFF] hover:bg-[#4752C4] dark:hover:bg-[#4752C4] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5865F2] dark:focus:ring-offset-gray-800 transform hover:-translate-y-0.5 transition-all duration-150 shadow-sm hover:shadow-md']) }}>
    <span class="text-[#FFFFFF] dark:text-[#FFFFFF]">{{ $slot }}</span>
</button>
