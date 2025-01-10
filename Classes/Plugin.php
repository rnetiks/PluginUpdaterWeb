<?php
class Plugin
{
	private $Id = -1, $Name = "Undefined", $Version = "0.0.0.0";
	private string $uid;

	private mysqli $connection;

	public function __construct()
	{
		$this->connection = new mysqli("localhost", "root", getenv("DB_PASSWORD", true), "pud");
	}

	public function GetVersions($uid)
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

    public function GetExtraInfo($uid)
    {
        $stmt = $this->connection->prepare("SELECT p.changelog, c.description, p.version FROM plugins p LEFT JOIN collections c ON p.uid_id = c.id WHERE c.uid = ? ORDER BY p.version DESC");
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

	public static function Recent()
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

	public static function GetLatest($uid)
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

	public static function GetFromVersion($uid, $version)
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
}

class Collection
{
	var mysqli $client;
	public function __construct()
	{
		$this->client = new mysqli("localhost", "root", getenv("DB_PASSWORD", true), "pud");
	}

	public function ExistsName($name)
	{
		$stmt = $this->client->prepare("SELECT COUNT(*) FROM Collections WHERE Name LIKE ?");
		$stmt->bind_param("s", $name);
		$stmt->execute();
		$res = $stmt->get_result();

		if ($res->num_rows > 0) {
			$row = $res->fetch_array();
			return (int) $row[0];
		}

		return 0;
	}

	public function ExistsUid($uid)
	{
		$stmt = $this->client->prepare("SELECT COUNT(*) FROM Collections WHERE uid LIKE ?");
		$stmt->bind_param("s", $uid);
		$stmt->execute();
		$res = $stmt->get_result();

		if ($res->num_rows > 0) {
			$row = $res->fetch_array();
			return (int) $row[0];
		}

		return 0;
	}

	public function CreateCollection($Name)
	{
		if ($this->ExistsName($Name) > 0) {
			return false;
		}

		$uid = $this->createHash();
		if ($this->ExistsUid($uid) > 0) {
			return false;
		}

		$stmt = $this->client->prepare("INSERT INTO Collections (Name, UID) VALUES (?,?)");

		if ($stmt == false) {
			return false;
		}

		$stmt->bind_param("ss", $Name, $uid);
		$stmt->execute();
		return $stmt->affected_rows > 0;
	}

	public static function createHash()
	{
		return bin2hex(random_bytes(8));
	}

	public function Search($name)
	{
		$stmt = $this->client->prepare("SELECT id, Name, UID FROM Collections WHERE Name like ? LIMIT 50");

		if ($stmt == false)
			return false;

		$name = "%" . $name . "%";
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

	public function GetName($uid)
	{
		$stmt = $this->client->prepare("SELECT Name FROM Collections WHERE Uid LIKE ?");
		$stmt->bind_param("s", $uid);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result->num_rows > 0) {
			return $result->fetch_array()[0];
		}

		return null;
	}
}