#!/bin/bash
PLUGINDIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PLUGINDIR"
LIBDIR="${LIBDIR:-/usr/local/lib}"
make clean || true
sudo rm -f "$LIBDIR/libfpp-co-SPIPixels.so"
sudo ldconfig
echo "Removed. Delete any SPIPixels entries from co-other.json and restart fppd."
