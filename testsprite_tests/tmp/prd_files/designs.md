
# Apple-Inspired Website Design Prompt

## Design Philosophy
Create a website that captures Apple's signature minimalist, premium, and sophisticated design language. The design should emphasize clean lines, generous white space, elegant typography, and a focus on content hierarchy.

## Color Palette
**Primary Colors:**
- Pure Black: `#000000` (0,0,0) - For primary text, headlines, and high-contrast elements
- Medium Gray: `#666666` (102,102,102) - For secondary text and subtle dividers
- Light Gray: `#979797` (151,151,151) - For tertiary text and placeholder content
- Off-White: `#eeeeee` (238,238,238) - For background sections and subtle containers
- Apple Blue: `#0088cc` (0,136,204) - For links, CTAs, and accent elements

**Liquid Glass Effects:**
Apply glassmorphism/liquid glass effects to small UI elements using:
- Semi-transparent backgrounds with `backdrop-filter: blur(20px)`
- Subtle border gradients using rgba values of the main palette
- Soft drop shadows with low opacity
- Frosted glass appearance with slight color tints

## Typography
- **Primary Font:** San Francisco (system font) or Helvetica Neue as fallback
- **Font Weights:** Light (300), Regular (400), Medium (500), Semi-bold (600)
- **Hierarchy:** Large headlines (48-72px), section titles (32-40px), body text (16-18px)
- **Line Height:** 1.4-1.6 for optimal readability
- **Letter Spacing:** Tight (-0.02em) for headlines, normal for body text

## Layout Structure
- **Grid System:** 12-column responsive grid with consistent gutters
- **Spacing:** Use multiples of 8px for consistent spacing (8px, 16px, 24px, 32px, 48px, 64px)
- **Max Width:** 1200px for main content areas
- **Margins:** Generous whitespace - minimum 80px top/bottom for sections
- **Alignment:** Center-aligned layouts with left-aligned text blocks

## Navigation Bar (Bootstrap 5 + Mobile Optimized)
- **Structure:** Use `.navbar .navbar-expand-lg .navbar-light` as base
- **Mobile Collapse:** Implement `.navbar-toggler` with custom hamburger icon
- **Glass Effect:** Apply to `.navbar` with custom CSS backdrop-filter
- **Mobile Height:** 70px for easy thumb reach, 80px on desktop
- **Collapse Animation:** Smooth slide-down animation for mobile menu
- **Logo Scaling:** Responsive logo sizing using Bootstrap's responsive utilities

```html
<nav class="navbar navbar-expand-lg fixed-top apple-glass-nav">
  <div class="container-xl">
    <a class="navbar-brand" href="#">Logo</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="#">Products</a></li>
      </ul>
    </div>
  </div>
</nav>
```

## Component Design Guidelines

### Bootstrap 5 Buttons (Apple Style)
```css
.btn-apple-primary {
  background: #0088cc;
  border: none;
  border-radius: 12px;
  padding: 12px 32px;
  font-weight: 500;
  min-height: 44px; /* Mobile touch target */
}

.btn-apple-glass {
  background: rgba(238, 238, 238, 0.1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(151, 151, 151, 0.2);
  border-radius: 12px;
  color: #000000;
  min-height: 44px;
}

@media (max-width: 576px) {
  .btn { width: 100%; margin-bottom: 1rem; } /* Full-width mobile buttons */
}
```

### Bootstrap 5 Cards (Apple Style)
```css
.card-apple {
  border: 1px solid rgba(151, 151, 151, 0.15);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.08);
  background: #ffffff;
}

.card-apple-glass {
  background: rgba(238, 238, 238, 0.1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(151, 151, 151, 0.2);
  border-radius: 16px;
}
```

### Mobile-Optimized Hero Sections (Bootstrap 5)
```html
<section class="hero-section py-5">
  <div class="container-xl">
    <div class="row justify-content-center text-center">
      <div class="col-12 col-lg-8">
        <h1 class="display-1 mb-4">Your Headline</h1>
        <p class="lead mb-4 col-12 col-md-10 mx-auto">Supporting text</p>
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
          <button class="btn btn-apple-primary">Primary Action</button>
          <button class="btn btn-apple-glass">Secondary Action</button>
        </div>
      </div>
    </div>
  </div>
</section>
```

### Content Sections (Mobile-First)
```html
<section class="py-5 py-lg-6">
  <div class="container-xl">
    <div class="row align-items-center">
      <div class="col-12 col-lg-6 mb-4 mb-lg-0">
        <h2 class="display-3 mb-3">Section Title</h2>
        <p class="lead">Content description</p>
      </div>
      <div class="col-12 col-lg-6">
        <img src="image.jpg" class="img-fluid rounded-4" alt="Description">
      </div>
    </div>
  </div>
</section>
```

## Interactive Elements
- **Hover States:** Subtle scale transforms (transform: scale(1.02)) and opacity changes
- **Transitions:** Smooth 0.3s ease-in-out for all interactions
- **Focus States:** Subtle blue outline using Apple blue color
- **Loading States:** Elegant skeleton screens or minimal spinners

## Liquid Glass Applications
Apply glassmorphism effects to:
- Navigation bar backdrop
- Floating action buttons
- Modal overlays
- Feature highlight boxes
- Testimonial cards
- Search bars
- Notification badges
- Floating toolbars

## Bootstrap 5 Mobile-First Implementation

### Bootstrap Grid System
- **Container:** Use `.container-fluid` or `.container-xxl` for full-width Apple-style layouts
- **Responsive Grid:** Leverage Bootstrap's 12-column grid with Apple-specific customizations
- **Breakpoints:** Follow Bootstrap 5 breakpoints - xs (<576px), sm (≥576px), md (≥768px), lg (≥992px), xl (≥1200px), xxl (≥1400px)
- **Column Classes:** Use `.col-12 .col-md-8 .col-lg-6` patterns for content width control

### Mobile-First Typography (Bootstrap 5)
```css
/* Custom CSS to override Bootstrap defaults with Apple styling */
.display-1 { font-size: 2.5rem; font-weight: 300; } /* Mobile headlines */
.display-2 { font-size: 2rem; font-weight: 300; }
.display-3 { font-size: 1.75rem; font-weight: 400; }

@media (min-width: 768px) {
  .display-1 { font-size: 4.5rem; } /* Desktop headlines */
  .display-2 { font-size: 3.5rem; }
  .display-3 { font-size: 2.5rem; }
}
```

### Bootstrap Component Customization
- **Navbar:** Use `.navbar-expand-lg` with custom glassmorphism styling
- **Cards:** Extend `.card` class with Apple-specific shadows and rounded corners
- **Buttons:** Override Bootstrap button styles with Apple design language
- **Spacing:** Use Bootstrap's spacing utilities (m-*, p-*) combined with custom Apple spacing scale

### Mobile Touch Optimization
- **Touch Targets:** Minimum 44px height for all interactive elements (.btn, .nav-link)
- **Spacing:** Use Bootstrap's responsive spacing (.mt-3 .mt-md-5 for progressive spacing)
- **Gestures:** Ensure swipe-friendly carousels and smooth scrolling
- **Viewport:** `<meta name="viewport" content="width=device-width, initial-scale=1">`

## Animation Guidelines
- **Micro-interactions:** Subtle animations for button hovers and state changes
- **Page Transitions:** Smooth, meaningful animations that guide user attention
- **Scroll Effects:** Parallax or fade-in effects for content reveals
- **Timing:** Use easing functions like `cubic-bezier(0.4, 0, 0.2, 1)`

## Accessibility Requirements
- **Contrast Ratios:** Minimum 4.5:1 for normal text, 3:1 for large text
- **Focus Indicators:** Clear, visible focus states for keyboard navigation
- **Alternative Text:** Descriptive alt text for all images
- **Semantic HTML:** Proper heading hierarchy and semantic markup
- **Color Independence:** Don't rely solely on color to convey information

## Mobile Performance & Bootstrap 5 Optimization

### CSS Custom Properties (Apple Colors)
```css
:root {
  --apple-black: #000000;
  --apple-gray: #666666;
  --apple-light-gray: #979797;
  --apple-off-white: #eeeeee;
  --apple-blue: #0088cc;
  --apple-glass-bg: rgba(238, 238, 238, 0.1);
  --apple-glass-border: rgba(151, 151, 151, 0.2);
}
```

### Bootstrap 5 Utilities Integration
- **Spacing:** Use custom spacing scale with Bootstrap (.py-apple-1 to .py-apple-6)
- **Colors:** Extend Bootstrap's color system with Apple palette
- **Typography:** Override Bootstrap's typography with Apple font stack
- **Shadows:** Custom shadow utilities (.shadow-apple-soft, .shadow-apple-medium)

### Mobile Loading & Performance
- **Critical CSS:** Inline critical Apple styles for above-the-fold content
- **Bootstrap Bundle:** Use minified Bootstrap 5.3+ with tree-shaking
- **Lazy Loading:** Apply `loading="lazy"` to images below the fold
- **Touch Optimization:** Ensure 300ms click delay is eliminated
- **Smooth Scrolling:** `scroll-behavior: smooth` for anchor links