# Changelog

## 1.0.0 (2026-11-03)
- Chips: APA102 / SK9822 / HD107S (+ HD brightness modes), HD108 / NS108 / SK9826 family (16-bit), TLC59711 / TLC5971, APA107, LC8822 / LC8823, WS2801 / WS2803, LPD8806 / LPD6803 families, P9813, SM16716 / SM16726.
- Reads the kernel's spidev transfer limit (`spidev.bufsiz`) instead of assuming 4096 bytes; TLC59711 chains are kept in one transfer and the plugin warns when one does not fit.
- Verified: unit tests, a 225-vector cross-check of every protocol against an independent framer, load on a BeagleBone Green (FPP 5.4.1), and measured send times (see the fpp-bbb-pixels repository, `PERFORMANCE.md`).
