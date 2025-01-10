<?php
$Plugin = new Plugin();
$collection = new Collection();
$args = Router::extractUrlParts("/plugins/$");
$uid = $args[0];
$name = $collection->GetName($uid);
if ($name == null) {
    header("Location: /");
    exit;
}

$versions = $Plugin->GetVersions($uid)
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $name ?></title>
    <link rel="stylesheet" href="/style.css">
</head>

<body>
<div class="loadscreen"><div class="bar"></div></div>
<div class="header"><a href=".." class="back">&lt; Keelhauled</a> | <?= $name ?></div>
<div class="flexcontainer">
    <div>
        <div class="select" style="width: 200px">
            <select name="" id="hrv">
                <?php
                foreach ($versions as $key => $value) {
                    echo "<option value=\"$value[version]\">$value[version]</option>";
                }
                ?>
            </select>
        </div>
        <a href="/plugins/<?= $uid ?>/download?version=<?= $versions[0]['version'] ?>" id="dlbt">
            <button class="download">Download</button>
        </a>
    </div>
    <div class="descriptionContainer">
        <fieldset>
            <legend>Description</legend>
            <div class="text">
                <?= $Plugin->GetDescription($uid) ?? "No description provided" ?>
            </div>
        </fieldset>
    </div>
    <div class="changelogContainer">
        <fieldset>
            <legend>Changelog</legend>
            <div class="text">
                <?= $Plugin->GetChangelog($uid) ?? "No changelog provided" ?>
            </div>
        </fieldset>
    </div>
</div>
<script>
    let uid = "<?=$uid?>";
    var x, i, j, l, ll, selected, a, b, c;
    let hrv = document.getElementById("hrv");
    x = document.getElementsByClassName("select");
    l = x.length;

    function SetAdditional(v) {
        let loader = document.querySelector(".loadscreen");
        loader.classList.add("active");
        fetch("http://localhost/plugins/<?=$uid?>/list", {
            method: "GET"
        }).then(response => response.json())
            .then(data => {
                let text = data[v];
                let changelog = text['changelog'] ?? "No changelog provided";
                let description = text['description'] ?? "No description provided";
                document.querySelector(".changelogContainer .text").innerText = changelog;
                document.querySelector(".descriptionContainer .text").innerText = description;
                loader.classList.remove("active");
            })
            .catch(e => {
                console.log(e);
            })
    }

    function versionChanged() {
        let version = hrv.options[hrv.options.selectedIndex].innerText;
        document.getElementById("dlbt").setAttribute("href", "/plugins/<?=$uid?>/download?version=" + version);
        SetAdditional(version);
    }

    for (i = 0; i < l; i++) {
        selected = x[i].getElementsByTagName("select")[0];
        ll = selected.length;
        a = document.createElement("div");
        a.setAttribute("class", "select-selected");
        a.innerHTML = selected.options[selected.selectedIndex].innerHTML;
        x[i].appendChild(a);
        b = document.createElement("div");
        b.setAttribute("class", "select-items select-hide");
        for (j = 0; j < ll; j++) {
            c = document.createElement("div");
            c.innerHTML = selected.options[j].innerHTML;
            c.addEventListener("click", function () {
                var y, i, k, s, h, sl, yl;
                s = this.parentNode.parentNode.getElementsByTagName("select")[0];
                sl = s.length;
                h = this.parentNode.previousSibling;
                for (i = 0; i < sl; i++) {
                    if (s.options[i].innerHTML == this.innerHTML) {
                        s.selectedIndex = i;
                        h.innerHTML = this.innerHTML;
                        y = this.parentNode.getElementsByClassName("same-as-selected");
                        yl = y.length;
                        for (k = 0; k < yl; k++) {
                            y[k].removeAttribute("class");
                        }
                        this.setAttribute("class", "same-as-selected");
                        versionChanged();
                        break;
                    }
                }
                h.click();
            });
            b.appendChild(c);
        }
        x[i].appendChild(b);
        a.addEventListener("click", function (e) {
            e.stopPropagation();
            closeAllSelect(this);
            this.nextSibling.classList.toggle("select-hide");
            this.classList.toggle("select-arrow-active");
        });
    }

    function closeAllSelect(e) {
        let x, y, i, xl, yl, arr = [];
        x = document.getElementsByClassName("select-items");
        y = document.getElementsByClassName("select-selected");
        xl = x.length;
        yl = y.length;
        for (i = 0; i < yl; i++) {
            if (e === y[i]) {
                arr.push(i);
            } else {
                y[i].classList.remove("select-arrow-active");
            }
        }
        for (i = 0; i < xl; i++) {
            if (arr.indexOf(i)) {
                x[i].classList.add("select-hide");
            }
        }
    }

    document.addEventListener("click", closeAllSelect);
</script>
</body>

</html>