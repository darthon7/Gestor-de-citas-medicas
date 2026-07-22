---
name: Clinical Clarity
colors:
  surface: '#FFFFFF'
  surface-dim: '#d8dadd'
  surface-bright: '#f7f9fc'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f7'
  surface-container: '#eceef1'
  surface-container-high: '#e6e8eb'
  surface-container-highest: '#e0e3e6'
  on-surface: '#191c1e'
  on-surface-variant: '#40484e'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eff1f4'
  outline: '#70787f'
  outline-variant: '#c0c7cf'
  surface-tint: '#0e658c'
  primary: '#005275'
  on-primary: '#ffffff'
  primary-container: '#1b6b93'
  on-primary-container: '#c7e7ff'
  inverse-primary: '#8bcefb'
  secondary: '#006a60'
  on-secondary: '#ffffff'
  secondary-container: '#8cf5e4'
  on-secondary-container: '#007166'
  tertiary: '#684600'
  on-tertiary: '#ffffff'
  tertiary-container: '#885c00'
  on-tertiary-container: '#ffdeaf'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#c7e7ff'
  primary-fixed-dim: '#8bcefb'
  on-primary-fixed: '#001e2e'
  on-primary-fixed-variant: '#004c6c'
  secondary-fixed: '#8cf5e4'
  secondary-fixed-dim: '#6fd8c8'
  on-secondary-fixed: '#00201c'
  on-secondary-fixed-variant: '#005048'
  tertiary-fixed: '#ffddaf'
  tertiary-fixed-dim: '#ffba42'
  on-tertiary-fixed: '#281800'
  on-tertiary-fixed-variant: '#614000'
  background: '#f7f9fc'
  on-background: '#191c1e'
  surface-variant: '#e0e3e6'
  primary-dark: '#0F4C6B'
  primary-light: '#A8D5E2'
  secondary-light: '#B5E8D5'
  danger: '#E76F51'
  danger-light: '#FADED4'
  text-primary: '#1A1A2E'
  text-secondary: '#4A5568'
  text-muted: '#A0AEC0'
  border: '#E2E8F0'
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: '700'
    lineHeight: 36px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Inter
    fontSize: 22px
    fontWeight: '600'
    lineHeight: 28px
  headline-sm:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '500'
    lineHeight: 24px
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Inter
    fontSize: 15px
    fontWeight: '600'
    lineHeight: 20px
  caption:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 4px
  gutter-md: 24px
  gutter-sm: 16px
  margin-edge: 24px
  sidebar-width: 260px
  header-height: 64px
---

## Brand & Style

The design system is anchored in the **Corporate / Modern** aesthetic, specifically tailored for the healthcare SaaS sector. It prioritizes "Information-Rich Minimalism," ensuring that dense medical data remains legible and stress-free. The brand personality is professional, calm, and profoundly trustworthy, aiming to reduce the anxiety often associated with medical scheduling.

By utilizing high whitespace and a systematic application of color, the UI evokes a sense of clinical precision without feeling cold or unapproachable. The visual language balances structural rigidity (grid-based layouts) with soft, human-centric details (rounded corners and subtle shadows) to foster a therapeutic digital environment.

## Colors

The color palette is functionally driven, moving beyond mere decoration to provide immediate semantic meaning. 

- **Primary (Blue)**: Represents trust and institutional stability. It is the dominant color for navigation and primary actions.
- **Secondary (Teal)**: Symbolizes healing and success. It is reserved for "confirmed" states and positive outcomes.
- **Tertiary/Accent (Amber)**: Used exclusively for "pending" states or items requiring immediate but non-critical attention.
- **Danger (Coral)**: Reserved for cancellations and critical errors.

The background uses a cool-toned gray (`#F7F9FC`) to reduce glare, while the white surface containers create clear "islands" of information, improving focus.

## Typography

This design system utilizes **Inter** exclusively to leverage its exceptional legibility and neutral, systematic tone. 

The typographic hierarchy is designed to handle high-density data. Headlines use tighter letter spacing and heavier weights to anchor sections, while body text maintains a generous line height (1.5x) to facilitate scanning of patient notes and appointment details. For mobile devices, headline sizes are automatically reduced to ensure titles do not wrap excessively, preserving the clean vertical rhythm of the interface.

## Layout & Spacing

The design system employs a **Fluid Grid** model with a base 8px rhythm (4px for micro-adjustments). 

- **Desktop**: A 12-column layout with 24px gutters. A fixed 260px sidebar provides persistent navigation, while the main content area expands.
- **Tablet**: Transition to a 8-column layout. The sidebar may collapse into a drawer to prioritize content width.
- **Mobile**: A single-column fluid layout with 16px side margins. 

Generous padding within cards (minimum 20px) is mandatory to maintain the "calm" aesthetic. Content should be grouped logically into cards rather than sprawling across the page, using the grid to align form fields and data tables.

## Elevation & Depth

Hierarchy is established through **Tonal Layers** and **Ambient Shadows**. This design system avoids heavy shadows, instead using them sparingly to indicate interactivity or distinct layering.

1.  **Level 0 (Background)**: The base application layer (`#F7F9FC`).
2.  **Level 1 (Surface)**: Cards and containers sit on the background using the primary shadow: `0 2px 12px rgba(27,107,147,0.08)`. The blue tint in the shadow maintains brand harmony.
3.  **Level 2 (Hover/Interaction)**: When cards or interactive elements are focused, the shadow deepens to `0 4px 20px rgba(27,107,147,0.14)` to provide tactile feedback.
4.  **Level 3 (Overlays)**: Modals and dialogs use the deepest shadow `0 8px 32px rgba(26,26,46,0.15)` and are accompanied by a semi-transparent backdrop overlay to kill background noise.

## Shapes

The shape language is **Rounded**, striking a balance between the precision of medical software and the approachability of a modern service. 

- **Standard Elements**: Buttons and input fields use a consistent 8px radius to feel organized and professional.
- **Containers**: Cards use a larger 12px radius to soften the overall appearance of the dashboard.
- **Mobile Optimizations**: On mobile, card radii increase to 16px and interactive chips are often pill-shaped to accommodate thumb-driven navigation.
- **Avatars**: Always circular (50% radius) to distinguish human elements from functional UI components.

## Components

### Buttons
- **Primary**: Solid `#1B6B93` with white text. High-contrast and identifiable.
- **Secondary**: Outlined with `#1B6B93` or soft-filled with `#A8D5E2` for lower-priority actions.
- **Danger**: Reserved for destructive actions (Cancellation). Uses `#E76F51`.

### Inputs
- **Text Fields**: 8px corner radius with a 1px `#E2E8F0` border. On focus, the border shifts to Primary Blue with a subtle 2px outer glow.
- **Labels**: Placed above the field in `label-md` style for maximum accessibility.

### Cards
- The foundational unit of the UI. Always white (`#FFFFFF`) with Level 1 elevation. Often features a 4px accent bar on the left edge to denote status (e.g., a green bar for a "Confirmed" appointment card).

### Chips & Badges
- Used for status indicators. Use "Soft Surface" colors (e.g., `Secondary Light` background with `Secondary` text) to ensure they are legible without being visually overwhelming.

### Lists & Tables
- Row-based with 1px horizontal dividers (`#E2E8F0`). Use `text-secondary` for metadata (date/time) and `text-primary` for the patient name.

### Status Indicators
- A pulsing dot indicator is used for real-time states like "In Consultation" to provide life to the interface without requiring a full page refresh.