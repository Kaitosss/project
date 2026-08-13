import conn from "../lib/db.js";

// Ensure table exists on server start
const initTable = async () => {
	try {
		const sql = `
		CREATE TABLE IF NOT EXISTS student (
			id INT AUTO_INCREMENT PRIMARY KEY,
			std_code VARCHAR(50) NOT NULL,
			std_fullname VARCHAR(255) NOT NULL,
			major VARCHAR(255) NOT NULL,
			level VARCHAR(50) NOT NULL,
			image VARCHAR(255) NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;`;
		await conn.query(sql);
	} catch (err) {
		console.error("Error initializing student table:", err);
	}
};
initTable();

export const getUsers = async (req, res) => {
	try {
		const sql = "SELECT * FROM student ORDER BY id DESC";
		const [rows] = await conn.query(sql);
		res.json(rows);
	} catch (error) {
		console.error(error);
		res.status(500).json({ message: "Database Error", error: error.message });
	}
};

export const createUser = async (req, res) => {
	try {
		const stdfullname = req.body?.stdfullname || req.body?.std_fullname;
		const stdcode = req.body?.stdcode || req.body?.std_code;
		const major = req.body?.major || "";
		const level = req.body?.level || "";
		const image = req.body?.image ?? null;

		if (!stdfullname || !stdcode) {
			return res.status(400).json({ message: "กรุณากรอกรหัสนักศึกษาและชื่อ-นามสกุล" });
		}

		const sql = "INSERT INTO student (std_fullname, std_code, major, level, image) VALUES (?,?,?,?,?)";
		const [result] = await conn.execute(sql, [stdfullname, stdcode, major, level, image]);

		res.status(201).json({ message: "Create success", id: result.insertId });
	} catch (error) {
		console.error(error);
		res.status(500).json({ message: "Internal Server Error", error: error.message });
	}
};

export const deleteUser = async (req, res) => {
	try {
		const { id } = req.params;
		const sql = "DELETE FROM student WHERE id = ?";
		await conn.execute(sql, [id]);
		res.json({ message: "Delete success" });
	} catch (error) {
		console.error(error);
		res.status(500).json({ message: "Internal Server Error", error: error.message });
	}
};
