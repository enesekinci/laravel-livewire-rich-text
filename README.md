# Laravel Livewire Rich Text

[TipTap](https://tiptap.dev) tabanlı ücretsiz (MIT) rich text editor — Laravel Livewire uyumlu.

Livewire morph sorununu `wire:ignore` + Alpine `@entangle` ile çözer. TipTap CDN (`esm.sh`) üzerinden yüklenir; Vite ayarı gerekmez.

## Kurulum

```bash
composer config repositories.rich-text vcs https://github.com/enesekinci/laravel-livewire-rich-text
composer require enesekinci/laravel-livewire-rich-text:^1.1
```

Gereksinim: Livewire 3/4 (Alpine dahil), `php artisan storage:link` (resim yükleme için).

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
| `enableImages` | `true` | Resim yükleme / URL |

Alias: `<x-admin.rich-text />`

Layout’ta CSRF meta olmalı:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

## Özellikler

- Geri / ileri
- Kalın, italik, altı çizili, üstü çizili, vurgu, satır içi kod
- H2 / H3, kod bloğu, yatay çizgi
- Madde / numaralı liste, alıntı
- Sola / ortala / sağa hizalama
- Link
- **Resim yükleme** (sunucuya) + resim URL
- Temizle

HTML olarak Livewire property’ye yazılır.

Resimler `storage/app/public/rich-text` altına kaydedilir; upload endpoint: `POST /__rich-text/upload` (auth zorunlu).

## Config

```bash
php artisan vendor:publish --tag=rich-text-config
```

## View override

```bash
php artisan vendor:publish --tag=rich-text-views
```

## Lisans

MIT (paket). TipTap da MIT.
