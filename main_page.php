<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
    <link rel="stylesheet" href="/style.css">
</head>

<body>
    <div class="header">Rnetiks</div>
    <table>
        <tr>
            <th>Recently Added</th>
        </tr>
        <tr>
            <th>Name</th>
            <th>Version</th>
            <th>Download</th>
        </tr>
        <?php
        foreach (Plugin::Recent() as $key => $value) {
            echo "<tr>";
            echo "<td>$value[name]</td>";
            echo "<td>$value[version]</td>";
            echo "<td><a href='/plugins/$value[uid]'>Link</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>

</html>