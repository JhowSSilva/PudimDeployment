# Pudim Design System

> Dark-first design system for the Pudim server deployment platform.
> Built with Tailwind CSS 3.x, Alpine.js 3.x, and Laravel Blade components.

---

## 1. Design Principles

| Principle | Description |
|---|---|
| **Dark-first** | `<html class="dark">`, `body.bg-neutral-900`. No `dark:` prefixes needed. |
| **Semantic Colors** | Use `primary-*`, `success-*`, `error-*`, `warning-*`, `info-*` — never raw `green-*`, `red-*`, etc. |
| **Component-driven** | All UI built from `<x-*>` Blade components. Prefer composition over inline classes. |
| **Accessible** | ARIA roles on all interactive elements. Focus rings on all clickable elements. `role="alert"` on feedback. |
| **Consistent Spacing** | Tailwind's 4px grid. Cards use `p-6`, sections use `space-y-6`, form groups use `space-y-4`. |

---

## 2. Color System

### 2.1 Brand / Primary (Amber)

| Token | Hex | Usage |
|---|---|---|
| `primary-400` | `#fbbf24` | Links, active nav items, icon accents |
| `primary-500` | `#f59e0b` | **Buttons, badges, focus rings** |
| `primary-600` | `#d97706` | Button hover (darker) |
| `primary-900/30` | — | Subtle bg for primary badges |

### 2.2 Neutral (Zinc-based)

| Token | Hex | Usage |
|---|---|---|
| `neutral-100` | `#f5f5f5` | **Heading text** |
| `neutral-300` | `#d4d4d4` | **Body text** |
| `neutral-400` | `#a3a3a3` | **Muted text, labels, placeholders** |
| `neutral-500` | `#737373` | Placeholder text in inputs |
| `neutral-600` | `#525252` | **Borders on inputs/buttons** |
| `neutral-700` | `#404040` | **Borders on cards, dividers, progress tracks** |
| `neutral-800` | `#262626` | **Card/surface background** |
| `neutral-900` | `#171717` | **Page background, table headers** |
| `neutral-950` | `#0a0a0a` | Backdrop overlays |

### 2.3 Semantic Status Colors

| Token | Text | Background | Border | Usage |
|---|---|---|---|---|
| **success** | `text-success-400` | `bg-success-900/30` | `border-success-600` | Online, active, deployed, healthy |
| **error** | `text-error-400` | `bg-error-900/30` | `border-error-600` | Offline, failed, expired, danger |
| **warning** | `text-warning-400` | `bg-warning-900/30` | `border-warning-600` | Provisioning, pending, expiring |
| **info** | `text-info-400` | `bg-info-900/30` | `border-info-600` | Running, in-progress, informational |

### 2.4 Forbidden Colors

**Never use these directly in templates:**

- `green-*`, `red-*`, `yellow-*`, `blue-*` — Use semantic tokens instead
- `amber-*` — Use `primary-*` (they are the same palette)
- `bg-white` — Use `bg-neutral-800` for surfaces
- `text-neutral-900` — Use `text-neutral-100` (dark-first)
- `text-neutral-700` — Use `text-neutral-300` (dark-first)
- `border-neutral-200/300` — Use `border-neutral-600/700`

---

## 3. Typography

### 3.1 Scale

| Size | Class | Font Size | Line Height | Usage |
|---|---|---|---|---|
| xs | `text-xs` | 0.75rem | 1rem | Badges, timestamps, char counts |
| sm | `text-sm` | 0.875rem | 1.25rem | **Default body text**, form labels |
| base | `text-base` | 1rem | 1.5rem | Paragraphs, descriptions |
| lg | `text-lg` | 1.125rem | 1.75rem | Section headings |
| xl | `text-xl` | 1.25rem | 1.75rem | Page sub-headings |
| 2xl | `text-2xl` | 1.5rem | 2rem | Page headings |
| 3xl | `text-3xl` | 1.875rem | 2.25rem | Hero headings |

### 3.2 Text Colors

| Purpose | Class |
|---|---|
| Page headings | `text-neutral-100 font-bold` |
| Section headings | `text-neutral-100 font-medium` |
| Body text | `text-neutral-300` |
| Muted / secondary | `text-neutral-400` |
| Placeholder | `placeholder:text-neutral-500` |
| Links | `text-primary-400 hover:text-primary-300` |

---

## 4. Spacing & Layout

### 4.1 Page Structure

```
<x-layout>                          ← Dark sidebar layout
  <div class="p-6 space-y-6">       ← Page content area
    <header>...</header>             ← Page header (title + actions)
    <div class="grid ...">          ← Content cards
  </div>
</x-layout>
```

### 4.2 Standard Spacing

| Context | Class |
|---|---|
| Page padding | `p-6` |
| Card padding | `p-6` |
| Section gap | `space-y-6` |
| Form group gap | `space-y-4` |
| Inline item gap | `gap-2` or `gap-3` |
| Label → input | `mb-1.5` (on label) |
| Input → error | `mt-1` (on error) |

### 4.3 Cards

```html
<div class="bg-neutral-800 rounded-lg shadow border border-neutral-700 p-6">
  <!-- content -->
</div>
```

---

## 5. Components

### 5.1 Buttons

```html
<x-button variant="primary">Save</x-button>
<x-button variant="secondary">Cancel</x-button>
<x-button variant="danger">Delete</x-button>
<x-button variant="ghost">More</x-button>
<x-button variant="success">Deploy</x-button>
<x-button variant="warning">Retry</x-button>
```

| Variant | Background | Text | Hover |
|---|---|---|---|
| primary | `bg-primary-500` | `text-neutral-900` | `bg-primary-400` |
| secondary | `bg-neutral-700` | `text-neutral-100` | `bg-neutral-600` |
| danger | `bg-error-600` | `text-white` | `bg-error-500` |
| ghost | transparent | `text-neutral-300` | `bg-neutral-800` |
| success | `bg-success-600` | `text-white` | `bg-success-500` |
| warning | `bg-warning-600` | `text-white` | `bg-warning-500` |

Sizes: `xs`, `sm` (default), `md`, `lg`

> **Deprecated:** `<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>` — delegate to `<x-button>`.

### 5.2 Form Controls

```html
<x-input-label for="name" value="Server Name" required />
<x-text-input id="name" name="name" :error="$errors->has('name')" />
<x-input-error :messages="$errors->get('name')" />
```

### 5.3 Modals

- `<x-modal>` — Base modal with backdrop, `role="dialog"`, `aria-modal="true"`
- `<x-confirm-modal>` — Confirmation dialog with icon and action buttons

### 5.4 Toasts

- `<x-toast-container>` — Auto-managed toast system
- Types: `success`, `error`, `warning`, `info`
- JS API: `toast.success('Title', 'Message')`

### 5.5 Status Badges

```html
<span class="badge-success">Online</span>
<span class="badge-error">Offline</span>
<span class="badge-warning">Pending</span>
<span class="badge-info">Running</span>
```

---

## 6. Accessibility

| Requirement | Implementation |
|---|---|
| Focus ring | `focus:ring-2 focus:ring-primary-500/50 focus:ring-offset-2 focus:ring-offset-neutral-900` |
| Modals | `role="dialog"`, `aria-modal="true"`, `aria-labelledby` |
| Toasts | `role="alert"`, `aria-live="assertive"` |
| Loading | `role="status"`, `aria-live="polite"` |
| Progress | `role="progressbar"`, `aria-valuenow/min/max` |
| Navigation | `role="navigation"`, `aria-label` |
| Required fields | Red asterisk via `required` prop on `<x-input-label>` |
| Form errors | `role="alert"` on `<x-input-error>` |

---

## 7. Transitions

| Animation | Duration | Usage |
|---|---|---|
| `transition-colors` | 200ms | Hover/focus states |
| `transition-all` | 300ms | Modals, panels |
| `animate-fadeIn` | 300ms | Toast entrance |
| `animate-slideUp` | 300ms | Modal entrance |
| `animate-slideDown` | 200ms | Dropdown entrance |

---

## 8. CSS Utility Classes

Defined in `resources/css/app.css`:

| Class | Description |
|---|---|
| `.surface-base` | Page background (`bg-neutral-900`) |
| `.surface-raised` | Card surface (`bg-neutral-800`) |
| `.surface-overlay` | Modal/dropdown (`bg-neutral-800 border border-neutral-700`) |
| `.text-heading` | `text-neutral-100` |
| `.text-body` | `text-neutral-300` |
| `.text-muted` | `text-neutral-400` |
| `.text-link` | `text-primary-400 hover:text-primary-300` |
| `.card` | Card with border and shadow |
| `.form-input` | Standard form input |
| `.badge-success/error/warning/info` | Status badges |
| `.focus-ring` | Standard focus ring |

---

## 9. Migration from Light Mode

1. `bg-white` → `bg-neutral-800`
2. `text-neutral-900` → `text-neutral-100`
3. `text-neutral-700` → `text-neutral-300`
4. `border-neutral-200` → `border-neutral-700`
5. `border-neutral-300` → `border-neutral-600`
6. `bg-neutral-50` → `bg-neutral-900`
7. `bg-neutral-100` → `bg-neutral-700`
8. Remove all `dark:*` prefixed classes
9. `amber-*` → `primary-*`
10. Raw `bg-green-100 text-green-800` → `bg-success-900/30 text-success-400`
11. `<x-primary-button>` → `<x-button variant="primary">`

---

*Design system version: 2.0 (dark-first redesign)*
