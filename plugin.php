<?php
/*
 * Config page for the SPIPixels plugin.
 *
 * SPIPixels channel outputs are stored as entries of type "SPIPixels" inside
 * FPP's co-other.json. FPP loads that file at startup and hands each entry to
 * this plugin's compiled output driver.
 *
 * To stay simple and robust this page edits the SPIPixels entry as JSON and
 * merges it back into co-other.json, leaving any other outputs untouched.
 */
global $settings;
$cfgDir  = isset($settings['configDirectory']) ? $settings['configDirectory'] : '/home/fpp/media/config';
$cfgPath = $cfgDir . '/co-other.json';

function spi_read_all($path) {
    if (!file_exists($path)) return array('channelOutputs' => array());
    $j = json_decode(file_get_contents($path), true);
    if (!is_array($j)) $j = array();
    if (!isset($j['channelOutputs']) || !is_array($j['channelOutputs'])) $j['channelOutputs'] = array();
    return $j;
}

$template = array(
    'type' => 'SPIPixels',
    'enabled' => 1,
    'startChannel' => 1,        // 1-based: first FPP channel this output covers
    'channelCount' => 900,      // total channels across all ports below
    'outputs' => array(
        array(
            'device' => 'spidev0.0',   // /dev/spidev0.0  (data=MOSI, clock=SCLK)
            'protocol' => 'apa102',    // apa102|sk9822|hd107s|apa102hd|sk9822hd|hd107shd|hd108|sk9826|tlc59711|apa107|ws2801|lpd8806|lpd6803|p9813|sm16716|sm16726
            'spiSpeed' => 8000000,     // Hz; omit to use the chip default
            'portNumber' => 0,
            'virtualStrings' => array(
                array(
                    'description' => 'String 1',
                    'startChannel' => 0,   // 0-based absolute channel (UI channel 1 == 0)
                    'pixelCount' => 300,
                    'groupCount' => 0,
                    'reverse' => 0,
                    'colorOrder' => 'BGR', // APA102 family is natively BGR
                    'nullNodes' => 0,
                    'endNulls' => 0,
                    'zigZag' => 0,
                    'brightness' => 100,
                    'gamma' => '1.0'
                )
            )
        )
    )
);

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cfg'])) {
    $entry = json_decode($_POST['cfg'], true);
    if (!is_array($entry)) {
        $err = 'Invalid JSON - nothing saved.';
    } else {
        $entry['type'] = 'SPIPixels';
        $all = spi_read_all($cfgPath);
        $kept = array();
        foreach ($all['channelOutputs'] as $co) {
            if (!isset($co['type']) || $co['type'] !== 'SPIPixels') $kept[] = $co;
        }
        $kept[] = $entry;
        $all['channelOutputs'] = $kept;
        if (file_put_contents($cfgPath, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
            $err = 'Could not write ' . htmlspecialchars($cfgPath) . ' (permissions?).';
        } else {
            $msg = 'Saved. Restart FPPD (Status/Control -> Restart FPPD) to apply.';
        }
    }
}

// Load the current SPIPixels entry for display (or the template if none).
$current = null;
$all = spi_read_all($cfgPath);
foreach ($all['channelOutputs'] as $co) {
    if (isset($co['type']) && $co['type'] === 'SPIPixels') { $current = $co; break; }
}
if ($current === null) $current = $template;
$cfgText = json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$tplText = json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
<div id="global" class="settings">
  <h2>SPI Pixels</h2>
  <p>Drive clocked SPI pixel chips over <code>/dev/spidev*</code> on Raspberry Pi or
     BeagleBone. Single-wire chips (WS2811/WS2812/SK6812/TM1814) are handled by FPP's
     core pixel string outputs, not this plugin.</p>

  <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>

  <div class="alert alert-warning" style="font-size:0.9em">
    <b>Heads up:</b> these outputs live in <code>co-other.json</code>. Configure them
    <i>here</i> - if you open <b>Status/Control &rarr; Other</b> and click Save there,
    FPP may drop the SPIPixels entry because the core page doesn't know the type.
  </div>

  <form method="post" action="plugin.php?plugin=SPIPixels&page=plugin.php&nopage=1">
    <p><b>SPIPixels channel output (JSON)</b></p>
    <textarea name="cfg" rows="26" style="width:100%;font-family:monospace;font-size:12px"><?php echo htmlspecialchars($cfgText); ?></textarea>
    <br><br>
    <input type="submit" class="buttons" value="Save">
    <input type="button" class="buttons" value="Load template"
           onclick="document.getElementsByName('cfg')[0].value = document.getElementById('tpl').textContent;">
  </form>

  <pre id="tpl" style="display:none"><?php echo htmlspecialchars($tplText); ?></pre>

  <h3>Field reference</h3>
  <ul style="font-size:0.9em">
    <li><b>startChannel</b> (output): 1-based first FPP channel. <b>channelCount</b>: total channels.</li>
    <li><b>device</b>: <code>spidev0.0</code>, <code>spidev0.1</code>, ... (Pi: enable SPI; BBB: SPI overlay).</li>
    <li><b>protocol</b>: <code>apa102</code>, <code>sk9822</code>, <code>hd107s</code>,
        <code>ws2801</code>, <code>lpd8806</code>, <code>lpd6803</code>, <code>p9813</code>,
        <code>sm16716</code>, <code>sm16726</code>, <code>hd108</code> (16-bit), <code>sk9826</code> (16-bit, order GRB), <code>tlc59711</code> (TI, 4 outputs per chip), <code>apa107</code>, <code>lc8822</code>, <code>lc8823</code>, and the high-definition-brightness
        modes <code>apa102hd</code>, <code>sk9822hd</code>, <code>hd107shd</code>. Aliases such as <code>ws2803</code>,
        <code>lpd8803</code>, <code>d705</code>, <code>apa102c</code> are accepted too.</li>
    <li><b>colorOrder</b>: RGB/RBG/GRB/GBR/BRG/<b>BGR</b>. APA102/SK9822/HD107S are natively BGR; P9813 BGR.</li>
    <li><b>virtualStrings[].startChannel</b>: <b>0-based</b> absolute channel (UI channel 1 = 0).</li>
    <li>brightness/gamma/groupCount/zigZag/reverse/nullNodes behave exactly like FPP pixel strings.</li>
  </ul>
</div>
