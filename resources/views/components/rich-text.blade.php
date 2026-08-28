@props([
    'label' => null,
    'placeholder' => 'Yazmaya başlayın…',
    'accent' => '#0b5cab',
    'minHeight' => '180px',
    'enableImages' => true,
])

@php
    $model = $attributes->wire('model');
    $uploadUrl = route('rich-text.upload');
@endphp

@once
    <script>
        window.richTextEditor = function (config) {
            let editor = null

            return {
                value: config.value,
                uploadUrl: config.uploadUrl,
                placeholder: config.placeholder,
                enableImages: config.enableImages,
                updatedAt: Date.now(),
                loading: true,
                uploading: false,
                error: null,

                async init() {
                        try {
                            const [
                                { Editor },
                                { default: StarterKit },
                                { default: Link },
                                { default: Placeholder },
                                { default: Underline },
                                { default: TextAlign },
                                { default: Highlight },
                                { default: Image },
                            ] = await Promise.all([
                                import('https://esm.sh/@tiptap/core@2.11.7'),
                                import('https://esm.sh/@tiptap/starter-kit@2.11.7'),
                                import('https://esm.sh/@tiptap/extension-link@2.11.7'),
                                import('https://esm.sh/@tiptap/extension-placeholder@2.11.7'),
                                import('https://esm.sh/@tiptap/extension-underline@2.11.7'),
                                import('https://esm.sh/@tiptap/extension-text-align@2.11.7'),
                                import('https://esm.sh/@tiptap/extension-highlight@2.11.7'),
                                import('https://esm.sh/@tiptap/extension-image@2.11.7'),
                            ])

                            const self = this
                            const extensions = [
                                StarterKit.configure({
                                    heading: { levels: [2, 3] },
                                }),
                                Underline,
                                Highlight.configure({ multicolor: false }),
                                TextAlign.configure({ types: ['heading', 'paragraph'] }),
                                Link.configure({
                                    openOnClick: false,
                                    HTMLAttributes: {
                                        class: 'rt-link',
                                        rel: 'noopener noreferrer',
                                        target: '_blank',
                                    },
                                }),
                                Placeholder.configure({
                                    placeholder: this.placeholder,
                                }),
                            ]

                            if (this.enableImages) {
                                extensions.push(Image.configure({
                                    allowBase64: false,
                                    HTMLAttributes: { class: 'rt-image' },
                                }))
                            }

                            editor = new Editor({
                                element: this.$refs.element,
                                extensions,
                                content: this.value || '',
                                editorProps: {
                                    attributes: { class: 'rt-prose' },
                                },
                                onUpdate({ editor: ed }) {
                                    self.value = ed.getHTML()
                                    self.updatedAt = Date.now()
                                },
                                onSelectionUpdate() {
                                    self.updatedAt = Date.now()
                                },
                                onCreate() {
                                    self.updatedAt = Date.now()
                                },
                            })

                            this.$watch('value', (html) => {
                                if (! editor || editor.isDestroyed) return
                                if (html === editor.getHTML()) return
                                editor.commands.setContent(html || '', false)
                            })
                        } catch (e) {
                            this.error = 'Editör yüklenemedi. İnternet / CDN erişimini kontrol edin.'
                            console.error(e)
                        } finally {
                            this.loading = false
                        }
                    },

                    destroy() {
                        editor?.destroy()
                        editor = null
                    },

                    isActive(type, opts = {}) {
                        this.updatedAt
                        return editor?.isActive(type, opts) ?? false
                    },

                    canUndo() {
                        this.updatedAt
                        return editor?.can().undo() ?? false
                    },

                    canRedo() {
                        this.updatedAt
                        return editor?.can().redo() ?? false
                    },

                    run(callback) {
                        if (! editor) return
                        callback(editor)
                        this.updatedAt = Date.now()
                    },

                    setLink() {
                        if (! editor) return
                        const previous = editor.getAttributes('link').href || 'https://'
                        const url = window.prompt('Bağlantı URL', previous)
                        if (url === null) return
                        if (url === '') {
                            editor.chain().focus().extendMarkRange('link').unsetLink().run()
                            return
                        }
                        editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
                        this.updatedAt = Date.now()
                    },

                    setImageUrl() {
                        if (! editor || ! this.enableImages) return
                        const url = window.prompt('Resim URL', 'https://')
                        if (! url) return
                        editor.chain().focus().setImage({ src: url }).run()
                        this.updatedAt = Date.now()
                    },

                    pickImage() {
                        if (! this.enableImages || this.uploading) return
                        this.$refs.imageInput?.click()
                    },

                    async uploadImage(event) {
                        if (! editor || ! this.enableImages) return
                        const file = event.target.files?.[0]
                        event.target.value = ''
                        if (! file) return

                        this.uploading = true
                        try {
                            const body = new FormData()
                            body.append('image', file)

                            const token = document.querySelector('meta[name=csrf-token]')?.content || ''
                            const response = await fetch(this.uploadUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                                body,
                            })

                            const data = await response.json().catch(() => ({}))
                            if (! response.ok) {
                                throw new Error(data.message || 'Yükleme başarısız.')
                            }
                            if (! data.url) {
                                throw new Error('Sunucu URL döndürmedi.')
                            }

                            editor.chain().focus().setImage({ src: data.url }).run()
                            this.updatedAt = Date.now()
                        } catch (e) {
                            window.alert(e.message || 'Resim yüklenemedi.')
                            console.error(e)
                        } finally {
                            this.uploading = false
                        }
                    },
            }
        }
    </script>
@endonce

<div
    wire:ignore
    style="--rt-accent: {{ $accent }}; --rt-min-h: {{ $minHeight }};"
    x-data="richTextEditor({
        value: @entangle($model).live,
        uploadUrl: {{ \Illuminate\Support\Js::from($uploadUrl) }},
        placeholder: {{ \Illuminate\Support\Js::from($placeholder) }},
        enableImages: {{ $enableImages ? 'true' : 'false' }},
    })"
    {{ $attributes->whereDoesntStartWith('wire:model')->class(['relative block space-y-1.5']) }}
>
    @if ($label)
        <span class="text-sm font-medium text-slate-600">{{ $label }}</span>
    @endif

    <input
        type="file"
        x-ref="imageInput"
        class="hidden"
        accept="image/jpeg,image/png,image/gif,image/webp"
        @change="uploadImage($event)"
    >

    <div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
        <div
            class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50 p-2"
            x-show="! loading && ! error"
        >
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().undo().run())" :disabled="! canUndo()" title="Geri al">Geri</button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().redo().run())" :disabled="! canRedo()" title="Yinele">İleri</button>
            <span class="rt-sep"></span>

            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleBold().run())" :class="{ 'rt-btn-active': isActive('bold') }" title="Kalın"><strong>B</strong></button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleItalic().run())" :class="{ 'rt-btn-active': isActive('italic') }" title="İtalik"><em>I</em></button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleUnderline().run())" :class="{ 'rt-btn-active': isActive('underline') }" title="Altı çizili"><span class="underline">U</span></button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleStrike().run())" :class="{ 'rt-btn-active': isActive('strike') }" title="Üstü çizili"><s>S</s></button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleHighlight().run())" :class="{ 'rt-btn-active': isActive('highlight') }" title="Vurgula">Vurgu</button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleCode().run())" :class="{ 'rt-btn-active': isActive('code') }" title="Satır içi kod">Kod</button>
            <span class="rt-sep"></span>

            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleHeading({ level: 2 }).run())" :class="{ 'rt-btn-active': isActive('heading', { level: 2 }) }" title="Başlık">H2</button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleHeading({ level: 3 }).run())" :class="{ 'rt-btn-active': isActive('heading', { level: 3 }) }" title="Alt başlık">H3</button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleCodeBlock().run())" :class="{ 'rt-btn-active': isActive('codeBlock') }" title="Kod bloğu">Blok</button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().setHorizontalRule().run())" title="Yatay çizgi">Çizgi</button>
            <span class="rt-sep"></span>

            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleBulletList().run())" :class="{ 'rt-btn-active': isActive('bulletList') }" title="Madde listesi">Liste</button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleOrderedList().run())" :class="{ 'rt-btn-active': isActive('orderedList') }" title="Numaralı liste">1. 2. 3.</button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleBlockquote().run())" :class="{ 'rt-btn-active': isActive('blockquote') }" title="Alıntı">Alıntı</button>
            <span class="rt-sep"></span>

            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().setTextAlign('left').run())" :class="{ 'rt-btn-active': isActive({ textAlign: 'left' }) }" title="Sola">Sol</button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().setTextAlign('center').run())" :class="{ 'rt-btn-active': isActive({ textAlign: 'center' }) }" title="Ortala">Orta</button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().setTextAlign('right').run())" :class="{ 'rt-btn-active': isActive({ textAlign: 'right' }) }" title="Sağa">Sağ</button>
            <span class="rt-sep"></span>

            <button type="button" class="rt-btn" @click="setLink()" :class="{ 'rt-btn-active': isActive('link') }" title="Bağlantı">Link</button>

            <template x-if="enableImages">
                <span class="inline-flex gap-1">
                    <button type="button" class="rt-btn" @click="pickImage()" :disabled="uploading" title="Resim yükle">
                        <span x-text="uploading ? 'Yükleniyor…' : 'Resim'"></span>
                    </button>
                    <button type="button" class="rt-btn" @click="setImageUrl()" title="Resim URL">URL</button>
                </span>
            </template>

            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().unsetAllMarks().clearNodes().run())" title="Biçimi temizle">Temizle</button>
        </div>

        <div x-show="loading" class="px-4 py-8 text-sm text-slate-500">Editör yükleniyor…</div>
        <div x-show="error" class="px-4 py-8 text-sm text-red-600" x-text="error"></div>

        <div
            x-ref="element"
            class="rt-editor"
            x-show="! loading && ! error"
        ></div>
    </div>

    <style>
        [wire\:ignore] .rt-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            border-radius: 0.5rem;
            border: 1px solid transparent;
            padding: 0.35rem 0.55rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            background: white;
            cursor: pointer;
        }
        [wire\:ignore] .rt-btn:hover:not(:disabled) { border-color: #cbd5e1; background: #f8fafc; }
        [wire\:ignore] .rt-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        [wire\:ignore] .rt-btn-active {
            border-color: var(--rt-accent);
            background: color-mix(in srgb, var(--rt-accent) 12%, white);
            color: var(--rt-accent);
        }
        [wire\:ignore] .rt-sep {
            width: 1px;
            align-self: stretch;
            background: #e2e8f0;
            margin: 0 0.15rem;
        }
        [wire\:ignore] .rt-editor .tiptap {
            min-height: var(--rt-min-h);
            padding: 0.85rem 1rem;
            outline: none;
        }
        [wire\:ignore] .rt-editor .tiptap p.is-editor-empty:first-child::before {
            color: #94a3b8;
            content: attr(data-placeholder);
            float: left;
            height: 0;
            pointer-events: none;
        }
        [wire\:ignore] .rt-prose p { margin: 0.4rem 0; }
        [wire\:ignore] .rt-prose h2 { font-size: 1.25rem; font-weight: 700; margin: 0.75rem 0 0.35rem; }
        [wire\:ignore] .rt-prose h3 { font-size: 1.1rem; font-weight: 650; margin: 0.65rem 0 0.3rem; }
        [wire\:ignore] .rt-prose ul { list-style: disc; padding-left: 1.25rem; margin: 0.4rem 0; }
        [wire\:ignore] .rt-prose ol { list-style: decimal; padding-left: 1.25rem; margin: 0.4rem 0; }
        [wire\:ignore] .rt-prose blockquote {
            border-left: 3px solid #cbd5e1;
            padding-left: 0.75rem;
            color: #64748b;
            margin: 0.5rem 0;
        }
        [wire\:ignore] .rt-prose a.rt-link,
        [wire\:ignore] .rt-prose a {
            color: var(--rt-accent);
            text-decoration: underline;
        }
        [wire\:ignore] .rt-prose mark {
            background: #fef08a;
            border-radius: 0.15rem;
            padding: 0 0.15rem;
        }
        [wire\:ignore] .rt-prose code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.85em;
            background: #f1f5f9;
            border-radius: 0.25rem;
            padding: 0.1rem 0.3rem;
        }
        [wire\:ignore] .rt-prose pre {
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            overflow-x: auto;
            margin: 0.5rem 0;
        }
        [wire\:ignore] .rt-prose pre code {
            background: transparent;
            color: inherit;
            padding: 0;
        }
        [wire\:ignore] .rt-prose hr {
            border: 0;
            border-top: 1px solid #cbd5e1;
            margin: 1rem 0;
        }
        [wire\:ignore] .rt-prose img.rt-image,
        [wire\:ignore] .rt-prose img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 0.5rem 0;
            display: block;
        }
    </style>
</div>
