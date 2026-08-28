@props([
    'label' => null,
    'placeholder' => 'Yazmaya başlayın…',
    'accent' => '#0b5cab',
    'minHeight' => '180px',
])

@php
    $model = $attributes->wire('model');
@endphp

<div
    wire:ignore
    style="--rt-accent: {{ $accent }}; --rt-min-h: {{ $minHeight }};"
    x-data="(() => {
        let editor = null

        return {
            value: @entangle($model).live,
            updatedAt: Date.now(),
            loading: true,
            error: null,

            async init() {
                try {
                    const [{ Editor }, { default: StarterKit }, { default: Link }, { default: Placeholder }] = await Promise.all([
                        import('https://esm.sh/@tiptap/core@2.11.7'),
                        import('https://esm.sh/@tiptap/starter-kit@2.11.7'),
                        import('https://esm.sh/@tiptap/extension-link@2.11.7'),
                        import('https://esm.sh/@tiptap/extension-placeholder@2.11.7'),
                    ])

                    const self = this

                    editor = new Editor({
                        element: this.$refs.element,
                        extensions: [
                            StarterKit,
                            Link.configure({
                                openOnClick: false,
                                HTMLAttributes: { class: 'rt-link' },
                            }),
                            Placeholder.configure({
                                placeholder: {{ \Illuminate\Support\Js::from($placeholder) }},
                            }),
                        ],
                        content: this.value || '',
                        editorProps: {
                            attributes: {
                                class: 'rt-prose',
                            },
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
        }
    })()"
    {{ $attributes->whereDoesntStartWith('wire:model')->class(['relative block space-y-1.5']) }}
>
    @if ($label)
        <span class="text-sm font-medium text-slate-600">{{ $label }}</span>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-sm">
        <div
            class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50 p-2"
            x-show="! loading && ! error"
        >
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleBold().run())" :class="{ 'rt-btn-active': isActive('bold') }" title="Kalın">
                <strong>B</strong>
            </button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleItalic().run())" :class="{ 'rt-btn-active': isActive('italic') }" title="İtalik">
                <em>I</em>
            </button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleStrike().run())" :class="{ 'rt-btn-active': isActive('strike') }" title="Üstü çizili">
                <s>S</s>
            </button>
            <span class="rt-sep"></span>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleHeading({ level: 2 }).run())" :class="{ 'rt-btn-active': isActive('heading', { level: 2 }) }" title="Başlık">
                H2
            </button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleHeading({ level: 3 }).run())" :class="{ 'rt-btn-active': isActive('heading', { level: 3 }) }" title="Alt başlık">
                H3
            </button>
            <span class="rt-sep"></span>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleBulletList().run())" :class="{ 'rt-btn-active': isActive('bulletList') }" title="Madde listesi">
                • Liste
            </button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleOrderedList().run())" :class="{ 'rt-btn-active': isActive('orderedList') }" title="Numaralı liste">
                1. Liste
            </button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().toggleBlockquote().run())" :class="{ 'rt-btn-active': isActive('blockquote') }" title="Alıntı">
                “”
            </button>
            <span class="rt-sep"></span>
            <button type="button" class="rt-btn" @click="setLink()" :class="{ 'rt-btn-active': isActive('link') }" title="Bağlantı">
                Link
            </button>
            <button type="button" class="rt-btn" @click="run(e => e.chain().focus().unsetAllMarks().clearNodes().run())" title="Temizle">
                Temizle
            </button>
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
        [wire\:ignore] .rt-btn:hover { border-color: #cbd5e1; background: #f8fafc; }
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
    </style>
</div>
