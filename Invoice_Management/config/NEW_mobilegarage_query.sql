-- Create table for storing customer information
create table Customers (
CustomerID integer not null AUTO_INCREMENT,
FirstName varchar(20) not null,
LastName varchar(20),
Company varchar(40),

Primary Key (CustomerID),
Unique (CustomerID)
);

-- Store multiple phone numbers for each customer
create table PhoneNumbers (
CustomerID integer not null AUTO_INCREMENT,
Nr varchar(15) not null,

foreign key (CustomerID) references Customers(CustomerID)
);

-- Store multiple email addresses for each customer
create table Emails (
CustomerID integer not null,
Emails varchar(100) not null,

foreign key (CustomerID) references Customers(CustomerID)
);

-- Store multiple addresses for each customer
create table Addresses (
CustomerID integer not null,
Address varchar(60) not null,

foreign key (CustomerID) references Customers(CustomerID)
);

-- Store vehicle information
create table Cars (
LicenseNr varchar(10) not null,
Brand varchar(50) not null,
Model varchar(50) not null,
VIN varchar(17) not null,
ManuDate date,
Fuel varchar(20),
KWHorse float,
Engine varchar(30),
KMMiles float,
Color varchar(30),
Comments varchar(500),

Primary Key (LicenseNr),
unique (VIN)
);

-- Store work orders/repair jobs
create table JobCards (
JobID integer not null AUTO_INCREMENT,
Location varchar(60) not null,
DateCall date,
JobDesc varchar(65535),
JobReport varchar(65535),
DateStart date,
DateFinish date,
Rides integer,
DriveCosts decimal(10,2),
Photo blob,

primary key (JobID),
unique (JobID)
);

-- Store supplier/vendor information
create table Suppliers (
SupplierID integer not null AUTO_INCREMENT,
Name varchar(40) not null,
PhoneNr varchar(15),
Email varchar(100),

primary key (SupplierID),
unique (SupplierID)
);

-- Store parts/inventory information
create table Parts (
PartID integer not null AUTO_INCREMENT,
SupplierID integer not null,
PartDesc varchar(30) not null,
PriceBulk decimal(10,2),
SellPrice decimal(10,2),
PiecesPurch integer not null,
PricePerPiece decimal(10,2) not null,
Vat decimal(10,2),
DateCreated date not null,
Sold integer default 0,
Stock integer default 1,

primary key (PartID),
unique (PartID),
foreign key (SupplierID) references Suppliers(SupplierID)
);

-- Store invoice information
create table Invoices (
InvoiceID integer not null AUTO_INCREMENT,
InvoiceNr integer,
DateCreated date,
Vat decimal(10,2),
Total decimal(10,2),
PDF blob,

primary key (InvoiceID),
unique (InvoiceID)
);

-- Store user accounts and access levels
create table Users (
username varchar(32) not null,
passwrd varchar(128) not null,
email varchar(100) not null,
admin boolean default 0,

primary key (username),
unique (username)
);

-- Link customers to their vehicles
create table CarAssoc (
CustomerID integer not null,
LicenseNr varchar(10) not null,

foreign key (CustomerID) references Customers(CustomerID),
foreign key (LicenseNr) references Cars(LicenseNr)
);

-- Link jobs to vehicles they were performed on
create table JobCar (
JobID integer not null,
LicenseNr varchar(10) not null,

foreign key (JobID) references JobCards(JobID),
foreign key (LicenseNr) references Cars(LicenseNr)
);

-- Link parts to their suppliers
create table PartSupplier (
PartID integer not null,
SupplierID integer not null,

foreign key (PartID) references Parts(PartID),
foreign key (SupplierID) references Suppliers(SupplierID)
);

-- Link jobs to their invoices
create table InvoiceJob (
JobID integer not null,
InvoiceID integer not null,

foreign key (JobID) references JobCards(JobID),
foreign key (InvoiceID) references Invoices(InvoiceID)
);

-- Track parts used in each job
create table JobCardParts (
JobID integer not null,
PartID integer not null,
PiecesSold integer not null,
PricePerPiece decimal(10,2) not null,

foreign key (JobID) references JobCards(JobID),
foreign key (PartID) references Parts(PartID)
);

-- Link parts to the invoices they were purchased on
create table PartsSupply (
InvoiceID integer not null,
PartID integer not null,

foreign key (InvoiceID) references Invoices(InvoiceID),
foreign key (PartID) references Parts(PartID)
);