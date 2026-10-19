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
                    _mediaRequestId: null,
                    _onMediaSelected: null,

                    async init() {
                        this._onMediaSelected = (e) => {
                            const detail = e.detail || {}
                            // Only the editor instance that opened the picker may insert.
                            if (! this._mediaRequestId || ! detail.requestId || detail.requestId !== this._mediaRequestId) {
                                return
                            }
                            if (! detail.url || ! editor) return
                            this._mediaRequestId = null
                            editor.chain().focus().setImage({ src: detail.url }).run()
                            this.updatedAt = Date.now()
                        }
                        window.addEventListener('media-selected', this._onMediaSelected)

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
                        if (this._onMediaSelected) {
                            window.removeEventListener('media-selected', this._onMediaSelected)
                        }
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
                        if (! this.enableImages) return
                        this._mediaRequestId = 'rt-' + Math.random().toString(36).slice(2)
                        window.dispatchEvent(new CustomEvent('open-media-picker', {
                            detail: { requestId: this._mediaRequestId },
                        }))
                    },
            }
        }
    </script>
