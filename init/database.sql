CREATE TABLE IF NOT EXISTS events (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      match_id VARCHAR(50) NOT NULL,
      team_id VARCHAR(50) NOT NULL,
      type VARCHAR(10) NOT NULL,
      payload JSON NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS statistics (
      match_id VARCHAR(50) NOT NULL,
      team_id VARCHAR(50) NOT NULL,
      event_type VARCHAR(10) NOT NULL,
      value INTEGER DEFAULT 0,
      PRIMARY KEY (match_id, team_id, event_type)
);