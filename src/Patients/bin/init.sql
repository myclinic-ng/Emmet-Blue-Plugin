INSERT INTO Patients.FieldTitleType (TypeName) VALUES ('Name'), ('Text'), ('Number'), ('Date'), ('File') 
INSERT INTO Patients.PatientRecordsFieldTitle (FieldTitleName, FieldTitleType) VALUES
('First Name', 'Name'),
('Last Name', 'Name'),
('Gender', 'Name'),
('Date Of Birth', 'Date'),
('Marital Status', 'Name'),
('Home Address', 'Text'),
('Mothers Maiden Name', 'Name'),
('Medical Hand Card Number', 'Name'),
('Phone Number', 'Name'),
('Reference Contact, Emergency', 'Text'),
('Reference Contact, Minor', 'Text'),
('State Of Origin', 'Name'),
('LGA', 'Name'),
('State Of Residence', 'Name'),
('Religious Affiliation', 'Name'),
('Occupation', 'Name'),
('Tribe', 'Name'),
('Email Address', 'Text'),
('Next Of Kin', 'Name'),
('Patient', 'Number'),
('HMO Number', 'Name')
INSERT INTO Patients.PatientRepositoryTypes VALUES
('prescription'),
('lab_result'),
('observation'),
('doctor_note'),
('payment_receipt')