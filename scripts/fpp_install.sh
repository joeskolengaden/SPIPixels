#!/bin/bash
# Build libfpp-co-SPIPixels.so against the locally-installed FPP and place it on
# the dynamic linker path so fppd can dlopen() it for "SPIPixels" outputs.
# Works on FPP 5.4 (createOutputSPIPixels) and 6.x-9.x (createPlugin) - the
# source auto-detects the API.
set -e
PLUGINDIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PLUGINDIR"
FPPDIR="${FPPDIR:-/opt/fpp}"
LIBDIR="${LIBDIR:-/usr/local/lib}"

echo "Building SPIPixels (FPPDIR=$FPPDIR)..."
make clean FPPDIR="$FPPDIR" || true
make FPPDIR="$FPPDIR"

echo "Installing libfpp-co-SPIPixels.so -> $LIBDIR"
sudo install -m 0755 libfpp-co-SPIPixels.so "$LIBDIR/"
sudo ldconfig

echo "Done. Configure outputs in the SPI Pixels plugin page, then Restart FPPD."
