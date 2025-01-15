<?php
require_once "Classes/User.php";
if (!User::IsLoggedIn() || !User::IsAdmin() || User::AdminRank() < User::RANK_ADMIN) {
    header("Location: /login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <style>
        th, td {
            text-align: center;
        }

        table {
            width: 40vw;
            min-width: 500px;
            table-layout: fixed;
        }

        button {
        }
    </style>
</head>
<body>
<table id="rn">
    <tr>
        <td colspan="3">
            <h3>Collections</h3>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" name="" id="colsearch" style="width: 75%; box-sizing: border-box;" placeholder="Search">
            <button>+</button>
        </td>
    </tr>
    <tr>
        <th>Name</th>
        <th>UID</th>
        <th>Action</th>
    </tr>
    <?php
    $client = new mysqli("localhost", "root", getenv("DB_PASSWORD"), "pud");
    $query = $client->query("SELECT c.Name, c.UID, COUNT(p.name) count FROM pud.collections c LEFT JOIN pud.plugins p ON p.uid_id = c.id group by c.Name, c.UID");
    if ($query->num_rows) {
        while ($row = $query->fetch_assoc()) {
            echo "<tr>";
            echo "<td>$row[Name] ($row[count])</td>";
            echo "<td>$row[UID]</td>";
            echo "<td><button class='sel'>Select</button> <a href=\"/collections/$row[UID]/delete\"><button>Delete</button></a></td>";
            echo "</tr>";
        }
    }
    ?>
    <tr>
        <td colspan="3" id="artemis"></td>
    </tr>
</table>
<script>
    $("#colsearch").on("input", () => {
        let t = $("#rn").find("tr");
        let searchValue = $(this).val().toLowerCase().trimStart();
        $(this).val(searchValue);

        let ss = 3;

        for(let i = ss; i < t.length; i++){
            let l = $(t[i]);
            l.children().first().text().toLowerCase().includes(searchValue) ? l.show() : l.hide();
        }
    })
    // document.getElementById("colsearch").addEventListener("input", function () {
    //     let t = document.getElementById("rn").querySelectorAll("tr");
    //     let searchValue = this.value.toLowerCase().trimStart();
    //     this.value = searchValue
    //     // Convert the search value to lowercase for case-insensitive comparison
    //
    //     let ss = 3
    //     // Hide all child elements starting from the second child (index 1)
    //     for (let i = ss; i < t.length; i++) {
    //         t[i].style.display = "none";
    //     }
    //
    //     // Show child elements that match the search value
    //     for (let i = ss; i < t.length; i++) {
    //         if (t[i].children[0].innerText.toLowerCase().includes(searchValue)) {
    //             t[i].style.display = ""; // Reset display to default
    //         }
    //     }
    // });

    for (let el of document.getElementsByClassName("sel")) {
        el.addEventListener("click", function () {
            let uid = this.parentNode.parentNode.children[1].textContent;
            for (let q of this.parentNode.parentNode.parentNode.children) {
                q.style.background = "";
                q.style.color = "black";
            }
            this.parentNode.parentNode.style.background = "#0077ff";
            this.parentNode.parentNode.style.color = "white";
            fetch(`http://localhost/plugins/${uid}/list`, {
                method: "GET"
            }).then(response => response.json()).then(data => {
                // Iterate over the keys of the object
                let container = document.getElementById("vrt");
                while (container.children.length > 1) {
                    container.children[1].remove()
                }
                for (let version in data) {
                    if (data.hasOwnProperty(version)) {
                        let item = data[version];
                        let row = document.createElement("tr");

                        let col1 = document.createElement("td");
                        col1.innerText = item['Name'] ?? "No Name";

                        let col2 = document.createElement("td");
                        col2.innerText = item['version'] ?? "No Version";

                        let col3 = document.createElement("td");
                        let delLink = document.createElement("a");
                        delLink.setAttribute("href", `/plugins/${uid}/${item['version']}/delete`);
                        delLink.onclick = function (eve) {
                            eve.stopPropagation();
                            eve.preventDefault();

                            const url = eve.currentTarget.getAttribute("href");
                            const parent = eve.currentTarget.parentNode.parentNode;
                            console.log(parent)
                            fetch(url, {
                                method: "GET"
                            }).then(response => {
                                if (response.status === 200) {
                                    parent.remove();
                                    Toastify({
                                        text: `${response.status}: ${response.statusText}`,
                                        duration: 3000,
                                        gravity: "top",
                                        position: "right",
                                        style: {
                                            background: "linear-gradient(to left, #00ff00, #30ff40)",
                                        }
                                    }).showToast()
                                } else {
                                    Toastify({
                                        text: `${response.status}: ${response.statusText}`,
                                        duration: 3000,
                                        gravity: "top",
                                        position: "right",
                                        style: {
                                            background: "linear-gradient(to left, #ff0000, #ff8000)",
                                        }
                                    }).showToast()
                                }
                            })
                        }
                        let delButton = document.createElement("button");
                        delButton.innerText = "Delete";

                        delLink.append(delButton);
                        col3.append(delLink);
                        row.append(col1);
                        row.append(col2);
                        row.append(col3);
                        container.append(row);
                    }
                }
            }).catch(error => console.error(error));
        })
    }
</script>
<table id="vrt">
    <tr>
        <td colspan="3">
            <h3>Plugins</h3>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" name="" id="" style="width: 100%; box-sizing: border-box;" placeholder="Search">
        </td>
    </tr>
    <tr>
        <th>Name</th>
        <th>Version</th>
        <th>Action</th>
    </tr>
</table>
</body>
</html>