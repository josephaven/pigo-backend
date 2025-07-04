@props(['name', 'class' => 'w-5 h-5'])

<svg xmlns="http://www.w3.org/2000/svg" 
     viewBox="0 0 24 24" 
     fill="none" 
     stroke="currentColor" 
     stroke-width="2" 
     stroke-linecap="round" 
     stroke-linejoin="round" 
     class="{{ $class }}">
    @include("components.icons.$name")
</svg>
