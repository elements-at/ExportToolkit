<h1>Export Toolkit</h1>
<form action="" method="post">
    <table>
        <tr>
            <td>
            <b>Use custom class list:</b>
            <input type="checkbox" name="override" value="1" <?php if ($this->config['classes']['override']) {
    ?> checked="checked" <?php
} ?>/>
            </td>
        </tr>
        <tr>
            <td>
            Class List:<br />
            <textarea name="classlist" style="width:1000px; height:200px;"><?= $this->config['classes']['classlist'] ?></textarea>
            </td>
        </tr>
        <tr>
            <td>
            Blacklist:<br />
            <textarea name="blacklist" style="width:1000px; height:200px;"><?= $this->config['classes']['blacklist'] ?></textarea><br>
            <input type="submit" />
            </td>
        </tr>
    </table>

</form>