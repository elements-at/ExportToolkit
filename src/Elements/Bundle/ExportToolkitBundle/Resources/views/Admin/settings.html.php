<?php
$config = $this->config;
$classlist = $config['classes']['classlist'];
$classlist = $classlist ? implode("\r\n", $classlist) : '';
$blacklist = $config['classes']['blacklist'];
$blacklist = $blacklist ? implode("\r\n", $blacklist) : '';
?>
<h1>Export Toolkit</h1>
<form action="" method="post">
    <table>
        <tr>
            <td>
            <b>Use custom class list:</b>
            <input type="checkbox" name="override" value="1" <?php if ($config['classes']['override']) {
    ?> checked="checked" <?php
} ?>/>
            </td>
        </tr>
        <tr>
            <td>
            Class List:<br />
            <textarea name="classlist" style="width:1000px; height:200px;"><?= $classlist ?></textarea>
            </td>
        </tr>
        <tr>
            <td>
            Blacklist:<br />
            <textarea name="blacklist" style="width:1000px; height:200px;"><?= $blacklist ?></textarea><br>
            <input type="submit" />
            </td>
        </tr>
    </table>

</form>
