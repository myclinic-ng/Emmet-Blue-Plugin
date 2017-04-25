--Insert four major Body Status types into the Mortuary.BodyStatus Table.

-- Items below are listed in (StatusShortCode, StatusName) table column order respectively
-- (RIP, Registration In Progress)
-- (LI, Logged In)
-- (LOP, Log Out In Progress)
-- (LO, Logged Out)

INSERT INTO Mortuary.BodyStatus VALUES
('RIP', 'Registration In Progress'),
('LI', 'Logged In'),
('LOP', 'Log Out In Progress'),
('LO', 'Logged Out')