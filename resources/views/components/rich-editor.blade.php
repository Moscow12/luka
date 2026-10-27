@props(['model', 'placeholder' => '', 'height' => '160px'])

{{-- Quill editor bound to a Livewire property (deferred, like wire:model). Output is sanitised server-side. --}}
<div wire:ignore {{ $attributes->merge(['class' => 'rich-editor bg-body']) }}
     x-data="{
        value: $wire.entangle('{{ $model }}'),
        quill: null,
        init() {
            if (typeof Quill === 'undefined') { return; }
            this.quill = new Quill(this.$refs.editor, {
                theme: 'snow',
                placeholder: @js($placeholder),
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link', 'blockquote', 'code-block'],
                        ['clean'],
                    ],
                },
            });
            this.quill.root.innerHTML = this.value || '';
            this.quill.on('text-change', () => {
                this.value = this.quill.getText().trim() === '' ? '' : this.quill.root.innerHTML;
            });
            // Clear the editor when the component resets the property (e.g. after sending)
            this.$watch('value', (v) => {
                if (! v && this.quill.getText().trim() !== '') { this.quill.setText(''); }
            });
        }
     }">
    <div x-ref="editor" style="min-height: {{ $height }}"></div>
</div>
