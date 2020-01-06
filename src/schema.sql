CREATE TABLE wp_otf_devices (
	device_description VARCHAR(100) DEFAULT '' NOT NULL,
	device_type        SMALLINT UNSIGNED,
	device_key         VARCHAR(32) UNIQUE      NOT NULL,
	user_id            BIGINT(20) UNSIGNED     NOT NULL,
	creation_date      DATETIME DEFAULT NOW()  NOT NULL,

	INDEX IX_user_id (user_id),

	CONSTRAINT UX_device_key UNIQUE (device_key),
	CONSTRAINT FK_user_id__wp_users FOREIGN KEY (user_id) REFERENCES wordpress.wp_users (ID)
) ENGINE = InnoDB;
