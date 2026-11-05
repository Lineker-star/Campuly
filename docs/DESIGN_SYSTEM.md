# Campuly design system

Built on Tailwind CSS 4 + the April UI kit. **All colours, radii and fonts come from tokens** in `resources/css/app.css` → section "CAMPULY BRAND THEME". Never put hex values in Blade views.

## 1. Tokens

April wraps tokens in `hsl()`, so colour tokens are HSL triplets.

| Token | Light | Dark | Use |
|---|---|---|---|
| `--background` | `230 50% 98%` | `234 55% 7%` | Page |
| `--foreground` | `234 60% 14%` | `230 50% 95%` | Text |
| `--card` | white | `234 48% 10%` | Cards, tables, dialogs |
| `--primary` / `-foreground` | indigo `241 90% 55%` / white | `248 95% 64%` / white | Primary actions, selected |
| `--secondary` | `232 60% 95%` | `234 40% 15%` | Secondary buttons, chips |
| `--muted` / `-foreground` | `232 45% 95%` / `234 16% 40%` | `234 40% 14%` / `232 22% 70%` | Quiet surfaces, helper text |
| `--accent` / `-foreground` | lavender / violet | deep violet / light violet | Hover, highlights, badges |
| `--destructive` | `0 72% 50%` | `0 72% 60%` | Delete, errors |
| `--border`, `--input` | `232 35% 89%` / `86%` | `234 35% 20%` / `22%` | Lines, fields |
| `--ring` | indigo | indigo light | Focus ring |
| `--radius` | `0.75rem` | | Base radius (sm = −4px, md = −2px, xl = +4px) |
| `--chart-1…5` | `#2E2CFC #0044FB #7109FC #0B135A #A100F8` | lighter variants | Charts, in this order |
| `--brand-link` | `241 80% 48%` | `245 100% 78%` | Text links |
| `--brand-gradient` | blue → indigo → violet → purple | same | `bg-brand-gradient`, primary buttons |

Tailwind colour utilities also available: `bg-brand-navy`, `text-brand-indigo`, `bg-brand-violet`, etc. Use them only for brand moments (hero, illustrations), not for UI states.

Status colours (add as tokens in Phase 2): success `#16A34A`, warning `#D97706`, danger `#DC2626`, info `#0284C7`, each with a 10 % tint background for pills.

## 2. Typography

`font-sans` = Plus Jakarta Sans Variable. Page title 28/36 semibold (mobile 22), section title 18 semibold, body 14–16 regular, labels 14 medium, helper 12–13 muted. Tabular figures in tables and numbers.

## 3. Layout

- Grid: 4 px base; spacing steps 4, 8, 12, 16, 24, 32, 48.
- Max content width `max-w-screen-2xl`; page padding 16 px mobile, 32 px desktop.
- Breakpoints: `sm 640`, `md 768`, `lg 1024`, `xl 1280`, `2xl 1536`. Design and test at 360, 768, 1280, 1920.
- Shell: sidebar 16 rem (icons-only 3 rem); header 56 px sticky; mobile bottom nav 64 px (Phase 2).

## 4. Components (gallery at `/dev/ui` from Phase 2)

| Component | Rules |
|---|---|
| Button | Primary = brand gradient, white text, one per view; Secondary, Outline, Ghost, Destructive; sizes sm/md/lg; loading state with spinner and disabled; icon buttons need `aria-label` |
| Inputs | Label above, helper below, error in destructive colour with icon; 44 px min touch height on mobile |
| Tables | Header row muted; zebra off; row hover accent; on < 768 px rows become cards; bulk-select column; sticky header |
| Cards / stat tiles | Card background, 1 px border, radius xl; stat value 28 semibold tabular, label muted, trend arrow coloured by status not by brand |
| Badges / status pills | Rounded-full, 12 px medium, tinted background + matching text |
| Dialogs / drawers | Title states the action and object; primary on the right; Escape closes; focus trapped |
| Toasts | Bottom-right desktop, top mobile; `aria-live="polite"`; Undo when reversible |
| Empty states | Icon/illustration, one sentence, one primary action |
| Skeletons | Same shapes as the content; never a blank screen |
| Charts | ECharts via `<x-chart>`; colours `--chart-1…5`; labels in the active locale; accessible table fallback |
| Logo | `<x-brand-logo>` only |

## 5. Motion

150–250 ms ease-out for hovers, menus, drawers; page progress bar in brand gradient; respect `prefers-reduced-motion`.

## 6. Accessibility (WCAG 2.2 AA)

Contrast ≥ 4.5:1 text, 3:1 UI; visible focus ring (indigo, 2 px, offset 2); full keyboard use; skip link (exists); labels on every input; `lang` attribute follows the locale; never colour alone for meaning; touch targets ≥ 44 px.

## 7. Responsive checklist (every screen)

- [ ] 360 px: no horizontal scroll, actions reachable, tables as cards
- [ ] 768 px: sidebar becomes drawer, two-column forms collapse
- [ ] 1280 px and 1920 px: content max width respected, no stretched lines over ~90 characters
- [ ] Light and dark, English and French (French text is ~20 % longer: buttons must not clip)
