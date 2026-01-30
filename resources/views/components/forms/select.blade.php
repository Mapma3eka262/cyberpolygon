@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'required' => false,
    'disabled' => false,
    'help' => null,
    'icon' => null,
    'placeholder' => 'Выберите...',
])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label">
            @if($icon)
                <i class="fas fa-{{ $icon }} me-1"></i>
            @endif
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    <select 
        name="{{ $name }}" 
        id="{{ $name }}" 
        class="form-select @error($name) is-invalid @enderror"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @foreach($options as $key => $option)
            @if(is_array($option))
                <option value="{{ $key }}" 
                    @if(old($name, $selected) == $key) selected @endif
                    @if(isset($option['disabled']) && $option['disabled']) disabled @endif
                >
                    {{ $option['label'] ?? $option }}
                </option>
            @else
                <option value="{{ $key }}" 
                    @if(old($name, $selected) == $key) selected @endif
                >
                    {{ $option }}
                </option>
            @endif
        @endforeach
    </select>
    
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
</div>