# AGENTS.md — laravel-livewire-rich-text

## Purpose
TipTap rich text for Livewire (`wire:ignore` + Alpine + `wire:model`).

## Hard rules for AI agents
1. Use `<x-rich-text wire:model.live="field" label="..." />` (alias `x-admin.rich-text`).
2. Do not build alternate WYSIWYG editors in apps that already require this package.
3. Images: open media picker (enesekinci/laravel-livewire-media). Do not use native file input for editor images when media package is present.
4. Keep CSRF meta tag in layout.
5. Store HTML in the model; sanitize on render with app helpers if needed.
6. Do not ask whether to use TipTap vs textarea for long formatted content — use this component.
7. Tailwind `@source` must include this package views.
