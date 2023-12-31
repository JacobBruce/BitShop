CREATE TABLE IF NOT EXISTS Products (
  FileID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  FileCat TINYTEXT NOT NULL,
  FileTags TINYTEXT NOT NULL,
  FileSales INT NOT NULL DEFAULT 0,
  FileStock FLOAT NOT NULL,
  FileActive BOOLEAN NOT NULL DEFAULT 1,
  FileName VARCHAR(50) NOT NULL,
  FileCode VARCHAR(50) NOT NULL,
  FileType VARCHAR(50) NOT NULL,
  FileDesc VARCHAR(10000) NOT NULL,
  FileMethod VARCHAR(10) DEFAULT 'download',
  FilePrice DECIMAL(12,4) NOT NULL,
  FileVoteSum INT UNSIGNED NOT NULL DEFAULT 0,
  FileVoteNum INT UNSIGNED NOT NULL DEFAULT 0,
  Created DATETIME,
  PRIMARY KEY (FileID)
);

CREATE TABLE IF NOT EXISTS Addresses (
  AddressID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  Country VARCHAR(60) NOT NULL,
  State VARCHAR(50) NOT NULL,
  Suburb VARCHAR(50) NOT NULL,
  Zipcode VARCHAR(10) NOT NULL,
  Address VARCHAR(80) NOT NULL,
  PRIMARY KEY (AddressID)
);

CREATE TABLE IF NOT EXISTS Accounts (
  AccountID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  AddressID MEDIUMINT UNSIGNED,
  Enabled BOOLEAN NOT NULL DEFAULT 1,
  PermGroup TINYINT UNSIGNED NOT NULL DEFAULT 0,
  FailCount TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PassHash VARCHAR(64) NOT NULL DEFAULT '',
  RealName VARCHAR(50) NOT NULL DEFAULT '',
  Email varchar(50) NOT NULL DEFAULT '',
  Phone VARCHAR(20) NOT NULL DEFAULT '',
  Settings TINYTEXT,
  LastIP TINYTEXT,
  LastTime DATETIME,
  Created DATETIME,
  PRIMARY KEY (AccountID),
  UNIQUE KEY (Email),
  FOREIGN KEY (AddressID) REFERENCES Addresses(AddressID)
);

CREATE TABLE IF NOT EXISTS Orders (
  OrderID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  AccountID MEDIUMINT UNSIGNED NOT NULL,
  Status VARCHAR(50) DEFAULT 'Unconfirmed',
  ShipStatus VARCHAR(50) DEFAULT 'Processing',
  Shipping DECIMAL(18,8) NOT NULL,
  Total DECIMAL(18,8) NOT NULL,
  Amount DECIMAL(18,8) NOT NULL DEFAULT 0,
  Currency VARCHAR(5) NOT NULL DEFAULT '',
  Cart VARCHAR(500) NOT NULL,
  Note VARCHAR(500),
  KeyData VARCHAR(500),
  Code VARCHAR(32),
  Address TINYTEXT,
  TranCode TINYTEXT,
  DatePaid DATETIME,
  Created DATETIME,
  PRIMARY KEY (OrderID),
  FOREIGN KEY (AccountID) REFERENCES Accounts(AccountID)
);

CREATE TABLE IF NOT EXISTS Codes (
  CodeID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ItemID MEDIUMINT UNSIGNED NOT NULL,
  OrderID MEDIUMINT UNSIGNED NOT NULL,
  AccountID MEDIUMINT UNSIGNED NOT NULL,
  Available BOOLEAN NOT NULL DEFAULT 1,
  CodeData TINYTEXT,
  Created DATETIME,
  PRIMARY KEY (CodeID),
  FOREIGN KEY (ItemID) REFERENCES Products(FileID)
);

CREATE TABLE IF NOT EXISTS Vouchers (
  VouchID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ItemID MEDIUMINT UNSIGNED NOT NULL,
  Name VARCHAR(50) NOT NULL,
  Discount DECIMAL(12,4) NOT NULL,
  Enabled BOOLEAN NOT NULL DEFAULT 1,
  Target BOOLEAN NOT NULL DEFAULT 0,
  UseType TINYINT NOT NULL DEFAULT 0,
  Credits INT UNSIGNED NOT NULL,
  CodeData VARCHAR(64) NOT NULL,
  PRIMARY KEY (VouchID),
  UNIQUE KEY (CodeData)
);

CREATE TABLE IF NOT EXISTS Reviews (
  RevID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ItemID MEDIUMINT UNSIGNED NOT NULL,
  Confirmed BOOLEAN NOT NULL DEFAULT 0,
  Rating TINYINT UNSIGNED NOT NULL,
  Author VARCHAR(50) NOT NULL,
  Review VARCHAR(1000),
  Created DATETIME,
  PRIMARY KEY (RevID),
  FOREIGN KEY (ItemID) REFERENCES Products(FileID)
);

CREATE TABLE IF NOT EXISTS Categories (
  CatID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  Parent MEDIUMINT UNSIGNED NOT NULL,
  CatPos SMALLINT UNSIGNED NOT NULL,
  Name TINYTEXT NOT NULL,
  Image TINYTEXT NOT NULL,
  Active BOOLEAN NOT NULL DEFAULT 1,
  PRIMARY KEY (CatID)
);

CREATE TABLE IF NOT EXISTS BtcAdds (
  AddID MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  Address VARCHAR(50) NOT NULL,
  Enabled BOOLEAN NOT NULL DEFAULT 1,
  PRIMARY KEY (AddID),
  UNIQUE KEY (Address)
);

DROP TRIGGER IF EXISTS Products_OnInsert;
CREATE TRIGGER Products_OnInsert BEFORE INSERT ON `Products`
FOR EACH ROW SET NEW.Created = IFNULL(NEW.Created, UTC_TIMESTAMP());

DROP TRIGGER IF EXISTS Orders_OnInsert;
CREATE TRIGGER Orders_OnInsert BEFORE INSERT ON `Orders`
FOR EACH ROW SET NEW.Created = IFNULL(NEW.Created, UTC_TIMESTAMP());

DROP TRIGGER IF EXISTS Codes_OnInsert;
CREATE TRIGGER Codes_OnInsert BEFORE INSERT ON `Codes`
FOR EACH ROW SET NEW.Created = IFNULL(NEW.Created, UTC_TIMESTAMP());

DROP TRIGGER IF EXISTS Reviews_OnInsert;
CREATE TRIGGER Reviews_OnInsert BEFORE INSERT ON `Reviews`
FOR EACH ROW SET NEW.Created = IFNULL(NEW.Created, UTC_TIMESTAMP());

DROP TRIGGER IF EXISTS Accounts_OnInsert;
CREATE TRIGGER Accounts_OnInsert BEFORE INSERT ON `Accounts`
FOR EACH ROW SET NEW.Created = IFNULL(NEW.Created, UTC_TIMESTAMP());