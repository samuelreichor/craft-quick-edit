# Release Notes for Quick Edit

## 5.3.0 - 2025-01-16
- Add CSP (Content Security Policy) compatibility
- Add `autoInject` setting to disable automatic injection of JS and CSS
- Add Twig variable `craft.quickEdit.render()` for manual template output
- Add optional nonce parameter `craft.quickEdit.render(nonce)` for CSP nonce support
- Add `craft.quickEdit.js()` and `craft.quickEdit.css()` for full control over output

## 5.2.2 - 2025-06-07
- Adjust installation behavior to set a cookie in dev mode.
  This ensures you don't have to log out and back in after installation.

## 5.2.1 - 2025-05-30
- Fix issue with standalone preview link generation

## 5.2.0 - 2025-04-08
- Add support for Verbb Social Login

## 5.1.0 - 2025-03-15
- Add support for Craft 4
- Add a bypass setting to always show the quick edit button

## 5.0.2 - 2025-01-26
- Add support for mutisites
- Add support for craft commerce product pages
- Improve performance by minimizing db queries

## 5.0.1 - 2025-01.24
- Add standalone preview mode setting for Craft versions >= 5.6.0
- Add a new cookie to check if the user was ever logged in to improve performance
- Add a new link text setting for more customization

## 5.0.0 - 2025-01.19
- Initial Release
