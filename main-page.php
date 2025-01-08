<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
    <style>
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
        }
        body{
            margin: 0;
            background:rgb(223, 223, 223);
        }
        .header{
            height: 60px;
            font-size: 24px;
            font-weight: bolder;
            font-family: Arial, Helvetica, sans-serif;
            padding-left: 15px;
            line-height: 60px;
            background-color: #3e3e3e;
            color: white;
        }
        table{
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 15px;
            background: white;
            box-shadow: 0px 0px 15px gray;
        }
    </style>
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
            echo "<td><a href='/plugins/$value[uid]/download'>Link</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
</body>

</html>