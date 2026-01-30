# 📱 Mobile Testing Guide

## Quick Start - Testing Mobile Responsiveness

### Browser DevTools (Quickest)

#### Chrome/Edge/Firefox
1. **Open DevTools**: Press `F12` or `Ctrl+Shift+I`
2. **Toggle Device Toolbar**: Press `Ctrl+Shift+M` (or click the device icon)
3. **Select Device**: Dropdown at top-left
4. **Test Orientations**: Press `Ctrl+Shift+M` to toggle portrait/landscape

#### Available Test Devices
- iPhone 12 (390×844)
- iPhone 13 Pro (390×844)
- Samsung Galaxy S10 (360×800)
- iPad (768×1024)
- iPad Pro (1024×1366)

---

## Manual Testing Checklist

### 1. **Touch Targets** ✓

**Test:** All buttons and interactive elements should be easy to tap

```
✓ Check Button Size
  - Open DevTools
  - Use "Inspect" tool
  - Buttons should show min-height: 44px minimum
  - Example: <button class="min-h-10"> (h-10 = 2.5rem = 40px)
  
✓ Practical Test
  - Can you tap a button without hitting adjacent elements?
  - Try smallest button in the interface
  - Should have at least 44×44px area
```

**Buttons to Check:**
- ✓ Course action buttons (Abrir, Editar, Deletar)
- ✓ Module expand/collapse
- ✓ Lesson navigation
- ✓ Form submission buttons
- ✓ Back buttons

---

### 2. **Font Sizes** ✓

**Test:** Inputs should not trigger auto-zoom on iOS

```
✓ Check Input Font
  - Inspect an input field
  - Font-size should be ≥ 16px
  - Example: <input class="px-4 py-3">
  
✓ Practical Test (Real iOS Device)
  - Open app on iPhone
  - Tap on any input
  - Should NOT zoom the page
```

**Elements to Check:**
- ✓ Text inputs (title, description)
- ✓ Textareas (quiz data)
- ✓ Select dropdowns (content type)
- ✓ Number inputs (order, duration)

---

### 3. **Responsive Layouts** ✓

**Test:** Layouts should adapt correctly at different widths

```
✓ Mobile (320px - 640px)
  - Courses: 1 column
  - Forms: Single column
  - Sidebar: Full width above content
  - Buttons: Full width

✓ Tablet (641px - 1024px)
  - Courses: 2 columns
  - Forms: 1-2 columns
  - Sidebar: Right side, sticky
  - Buttons: Auto width

✓ Desktop (1025px+)
  - Courses: 3 columns
  - Forms: 2 columns
  - Sidebar: Right side, sticky
  - Buttons: Inline

Testing Process:
1. Open DevTools
2. Set width to 375px (mobile)
3. Verify 1-column layout
4. Resize to 768px (tablet)
5. Verify 2-column layout
6. Resize to 1024px (desktop)
7. Verify 3-column layout
```

**Layouts to Check:**
- ✓ Courses Index Page
- ✓ Course Detail Page
- ✓ Lesson Detail Page
- ✓ Forms (Create/Edit pages)

---

### 4. **Video Responsiveness** ✓

**Test:** Video players should maintain aspect ratio and be responsive

```
✓ Check Video Player
  - Video should not overflow container
  - Should maintain 16:9 aspect ratio
  - Controls should be accessible
  - Full-screen should work
  
✓ Test at Different Widths
  - 375px width: Video fits screen
  - 640px width: Video fits with margins
  - 1024px width: Video scales properly

✓ Practical Test
  - Play a lesson with video
  - Try different screen widths
  - Press full-screen button
  - Try fast-forward/rewind
```

---

### 5. **Notch Support (iPhone X+)** ✓

**Test:** Safe area insets for notch devices

```
✓ Test on iPhone X, iPhone 12, iPhone 13
  - Header should not be covered by notch
  - Bottom safe area respected
  - Content not hidden behind notch
  
✓ Technical Check
  - Open DevTools on iPhone
  - Inspect header element
  - Should have padding from safe-area-inset
```

---

### 6. **Dark Mode** ✓

**Test:** Application should work in dark mode

```
✓ Enable Dark Mode
  - DevTools → Rendering → Emulate CSS media feature prefers-color-scheme
  - Select "dark"
  
✓ Visual Check
  - Background becomes dark
  - Text becomes light
  - Contrast is still readable
  - Buttons still visible
  
✓ Real Device Test
  - iPhone: Settings → Display & Brightness → Dark
  - Android: Settings → Display → Dark Theme
```

---

### 7. **Landscape Orientation** ✓

**Test:** Layout should work in landscape mode

```
✓ DevTools Test
  - Press Ctrl+Shift+M to toggle portrait/landscape
  - Content should adapt
  
✓ Practical Test
  - Rotate device to landscape
  - Check form visibility
  - Check video player
  - Check sidebar position
  - Check button accessibility
```

---

### 8. **Performance** ✓

**Test:** Page should load quickly on mobile networks

```
✓ Network Throttling
  - DevTools → Network → Slow 4G
  - Refresh page
  - Should load in reasonable time
  
✓ Lighthouse Score
  - DevTools → Lighthouse
  - Run report for mobile
  - Target scores:
    - Performance: > 80
    - Accessibility: > 90
    - Best Practices: > 90
```

---

## Real Device Testing

### iOS (iPhone/iPad)

**Setup:**
1. Connect device to computer
2. Open Safari on device
3. Go to `http://[your-computer-ip]:8000`

**Test:**
- ✓ Tap all buttons
- ✓ Scroll through courses
- ✓ Open lesson
- ✓ Play video
- ✓ Fill and submit form
- ✓ Check progress bars

### Android

**Setup:**
1. Connect via USB or local network
2. Open Chrome on device
3. Navigate to app

**Test:**
- ✓ Same as iOS
- ✓ Test on different Android versions
- ✓ Test on different screen sizes

---

## Common Issues & Fixes

### Issue: Text too small
```
Check: font-size in CSS
Fix: Add breakpoint for mobile text
@media (max-width: 640px) {
    p { font-size: 16px; }
}
```

### Issue: Buttons hard to click
```
Check: min-height and padding
Fix: Ensure min-h-10 (40px) or higher
<button class="min-h-10 px-4 py-3">
```

### Issue: Input zooms on iOS
```
Check: font-size on input
Fix: Ensure font-size >= 16px
<input style="font-size: 16px">
```

### Issue: Notch overlaps content
```
Check: padding on header
Fix: Add safe-area-inset
padding-top: env(safe-area-inset-top);
```

### Issue: Video doesn't scale
```
Check: max-width
Fix: Add max-width: 100%
<video style="max-width: 100%">
```

---

## Testing Commands

### Local Testing
```bash
# Start development server
php artisan serve

# On another machine, access:
http://[your-ip]:8000
```

### Chrome DevTools Emulation
```
1. F12 (open DevTools)
2. Ctrl+Shift+M (toggle device toolbar)
3. Click device selector
4. Choose device or custom size
```

### Lighthouse Report
```
1. DevTools → Lighthouse
2. Select "Mobile"
3. Click "Analyze page load"
4. Review scores and recommendations
```

---

## Performance Benchmarks

**Target Metrics:**
- First Contentful Paint (FCP): < 1.5s
- Largest Contentful Paint (LCP): < 2.5s
- Cumulative Layout Shift (CLS): < 0.1
- Time to Interactive (TTI): < 3.5s

**How to Check:**
1. DevTools → Performance → Record
2. Refresh page
3. Stop recording
4. Check metrics in summary

---

## Accessibility Testing

### Screen Reader (iOS)
1. Settings → Accessibility → VoiceOver
2. Triple-tap to activate
3. Navigate using gestures
4. Verify all elements are readable

### Screen Reader (Android)
1. Settings → Accessibility → TalkBack
2. Enable TalkBack
3. Swipe to navigate
4. Verify all elements are readable

### Color Contrast
- Use DevTools Accessibility panel
- Check contrast ratio
- Target: 4.5:1 for normal text

---

## Sign-Off Checklist

After completing all tests, confirm:

- [ ] All buttons are 44×44px minimum
- [ ] Inputs are 16px font-size
- [ ] Layouts respond at breakpoints (375px, 768px, 1024px)
- [ ] Videos maintain aspect ratio
- [ ] Notch devices work correctly
- [ ] Dark mode is functional
- [ ] Landscape orientation works
- [ ] Forms are accessible
- [ ] Performance is adequate (Lighthouse > 80)
- [ ] No console errors
- [ ] Tested on real devices (iPhone/Android)

---

## Additional Resources

- [Chrome DevTools Mobile Emulation](https://developer.chrome.com/docs/devtools/device-mode/)
- [Lighthouse](https://developers.google.com/web/tools/lighthouse)
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- [Apple HIG - Spacing](https://developer.apple.com/design/human-interface-guidelines/ios/visual-design/spacing/)
- [Material Design - Touch Targets](https://material.io/design/platform-guidance/android-bars.html)

---

**Last Updated:** 2025-01-30
**Version:** 1.0
