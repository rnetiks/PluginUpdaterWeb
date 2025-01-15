<?php

class Plugin
{
    private mysqli $connection;

    public function __construct()
    {
        $this->connection = new mysqli("localhost", "root", getenv("DB_PASSWORD", true), "pud");
    }

    public static function Create(): Plugin
    {
        return new Plugin();
    }

    public function GetVersions($uid): array
    {
        $stmt = $this->connection->prepare("SELECT p.version FROM plugins p LEFT JOIN collections c ON p.uid_id = c.id WHERE c.uid = ? ORDER BY p.version DESC");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    public function GetExtraInfo($uid): array
    {
        $stmt = $this->connection->prepare("SELECT p.changelog, c.description, p.version, c.Name FROM plugins p LEFT JOIN collections c ON p.uid_id = c.id WHERE c.uid = ? ORDER BY p.version DESC");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[$row['version']] = $row;
            }
        }
        return $rows;
    }

    public function GetDescription($uid)
    {
        $stmt = $this->connection->prepare("SELECT c.description FROM collections c WHERE c.uid = ?");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['description'];
        }
        return null;
    }

    public function GetChangelog($uid)
    {
        $stmt = $this->connection->prepare("SELECT p.changelog FROM plugins p LEFT JOIN collections c ON p.uid_id = c.id WHERE c.UID = ?");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['changelog'];
        }
        return null;
    }

    public static function Recent(): array
    {
        $rows = [];
        $client = new mysqli("localhost", "root", getenv("DB_PASSWORD", true), "pud");
        $result = $client->query("SELECT a.name, a.version, b.uid FROM plugins as a LEFT JOIN collections as b ON a.uid_id = b.id GROUP BY a.name, a.id ORDER BY a.id DESC LIMIT 40;");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public static function GetLatest($uid): bool|array|null
    {
        $client = new mysqli("localhost", "root", getenv("DB_PASSWORD", true), "pud");
        $stmt = $client->prepare("SELECT a.name, a.version, a.path FROM plugins a LEFT JOIN collections b ON a.uid_id = b.id WHERE b.UID = ? ORDER BY a.version DESC");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public static function GetFromVersion($uid, $version): bool|array|null
    {
        $client = new mysqli("localhost", "root", getenv("DB_PASSWORD", true), "pud");
        $stmt = $client->prepare("SELECT a.name, a.version, a.path FROM plugins a LEFT JOIN collections b ON a.uid_id = b.id WHERE b.UID = ? AND a.version = ? ORDER BY a.version DESC");
        $stmt->bind_param("ss", $uid, $version);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }

    public function Delete(mixed $uid, mixed $version)
    {
        $stmt = $this->connection->prepare("DELETE p FROM plugins p INNER JOIN collections c ON p.uid_id = c.id WHERE c.UID = ? AND p.version = ?");
        $stmt->bind_param("ss", $uid, $version);
        $stmt->execute();
        return $stmt->affected_rows;
    }

    public function DeleteAll(string $uid)
    {
        $stmt = $this->connection->prepare("DELETE p FROM plugins p INNER JOIN collections c ON p.uid_id = c.id WHERE c.UID = ?");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        return $stmt->affected_rows;
    }
}

class Collection
{
    var mysqli $connection;

    public function __construct()
    {
        $this->connection = new mysqli("localhost", "root", getenv("DB_PASSWORD", true), "pud");
    }

    public static function Create(): Collection
    {
        return new Collection();
    }

    public function ExistsName($name): int
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM Collections WHERE Name LIKE ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_array();
            return (int)$row[0];
        }

        return 0;
    }

    public function ExistsUid($uid): int
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM Collections WHERE uid LIKE ?");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_array();
            return (int)$row[0];
        }

        return 0;
    }

    /**
     * @throws \Random\RandomException
     */
    public function CreateCollection($Name, $Author, $Description): bool
    {
        if ($this->ExistsName($Name) > 0) {
            return false;
        }

        $uid = $this->createHash();
        if ($this->ExistsUid($uid) > 0) {
            return false;
        }

        $stmt = $this->connection->prepare("INSERT INTO collections (Name, UID, description, Author) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $Name, $uid, $Description, $Author);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    /**
     * @throws \Random\RandomException
     */
    public static function createHash(): string
    {
        return bin2hex(random_bytes(8));
    }

    public function Search(string $name): bool|array
    {
        $stmt = $this->connection->prepare("SELECT id, Name, UID FROM Collections WHERE Name like ? LIMIT 50");

        if (!$stmt)
            return false;

        $name = str_replace("*", "%", $name);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public function SearchHash(string $hash): bool|array
    {
        $stmt = $this->connection->prepare("SELECT c.id, c.Name, c.UID FROM Collections c LEFT JOIN pud.plugins p ON p.uid_id = c.id WHERE p.hash like ? LIMIT 50");

        if (!$stmt)
            return false;

        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public function GetName($uid): mixed
    {
        $stmt = $this->connection->prepare("SELECT c.Name FROM Collections c WHERE c.UID LIKE ?");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_array()[0];
        }

        return null;
    }

    public function Delete(string $uid)
    {
        $stmt = $this->connection->prepare("DELETE c FROM collections c WHERE c.UID = ?");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        return $stmt->affected_rows;
    }
}