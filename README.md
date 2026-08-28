# Laravel Livewire Rich Text

[TipTap](https://tiptap.dev) tabanlı ücretsiz (MIT) rich text editor — Laravel Livewire uyumlu.

Livewire morph sorununu `wire:ignore` + Alpine `@entangle` ile çözer. TipTap CDN (`esm.sh`) üzerinden yüklenir; Vite ayarı gerekmez.

## Kurulum

```bash
composer config repositories.rich-text vcs https://github.com/enesekinci/laravel-livewire-rich-text
composer require enesekinci/laravel-livewire-rich-text:^1.0
```

Gereksinim: Livewire 3/4 (Alpine dahil).

## Kullanım

```blade
<x-rich-text
    wire:model.live="description"
    label="Açıklama"
    placeholder="Yazmaya başlayın…"
/>
```

Seçenekler:

| Prop | Varsayılan | Açıklama |
|---|---|---|
| `label` | `null` | Üst etiket |
| `placeholder` | `Yazmaya başlayın…` | Boş içerik metni |
| `accent` | `#0b5cab` | Toolbar aktif rengi |
| `minHeight` | `180px` | Editör yüksekliği |

Alias: `<x-admin.rich-text />`

## Özellikler

- Kalın / italik / üstü çizili
- H2 / H3
- Madde ve numaralı liste
- Alıntı
- Link
- Temizle

HTML olarak Livewire property’ye yazılır (`$this->description`).

## View override

```bash
php artisan vendor:publish --tag=rich-text-views
```

## Lisans

MIT (paket). TipTap da MIT.
