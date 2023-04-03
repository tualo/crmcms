DELIMITER //

CREATE OR REPLACE FUNCTION `test_generic_login`(
    pw varchar(255),
    pwtype varchar(255),
    pwhash  varchar(255),
    salt  varchar(255)
) RETURNS INT(4)
DETERMINISTIC
BEGIN
    IF pwtype='md5' THEN
        IF md5(pw) = pwhash THEN 
            RETURN 1;
        END IF;
    ELSEIF pwtype='plain' THEN
        IF pw = pwhash THEN 
            RETURN 1;
        END IF;
    ELSEIF pwtype='saltedsha2' THEN
        IF sha2(concat(salt,pw),512) = pwhash THEN 
            RETURN 1;
        END IF;
    END IF;

    RETURN 0;
END //


CREATE OR REPLACE FUNCTION `test_translator_login`(in_username varchar(255),`in_password` varchar(255)) RETURNS int(4)
    READS SQL DATA
BEGIN
  DECLARE pwtype varchar(15);
  FOR record IN (select * from uebersetzer_logins where login = in_username) DO
    RETURN (select test_generic_login(
        in_password,
        record.passwordtype,
        record.password,
        record.salt
    ) 
    );
  END FOR;
  RETURN -2;
END //

CREATE OR REPLACE FUNCTION `test_employee_login`(in_username varchar(255),`in_password` varchar(255)) RETURNS int(4)
    READS SQL DATA
BEGIN
  DECLARE pwtype varchar(15);
  FOR record IN (select * from angestellten_logins where login = in_username) DO
    RETURN (select test_generic_login(
        in_password,
        record.passwordtype,
        record.password,
        record.salt
    ) 
    );
  END FOR;
  RETURN -2;
END //

CREATE OR REPLACE FUNCTION `test_customer_login`(in_username varchar(255),`in_password` varchar(255)) RETURNS int(4)
    READS SQL DATA
BEGIN
  DECLARE pwtype varchar(15);
  FOR record IN (select * from adressen_logins where login = in_username) DO
    RETURN (select test_generic_login(
        in_password,
        record.passwordtype,
        record.password,
        record.salt
    ) 
    );
  END FOR;
  RETURN -2;
END //

CREATE OR REPLACE FUNCTION `test_crm_login`(in_username varchar(255),`in_password` varchar(255)) 
RETURNS JSON
    READS SQL DATA
BEGIN
    DECLARE val int(4);
    SET val = test_customer_login(in_username,in_password);
    IF val=1 THEN 
        RETURN json_object(
            'success', 1=1,
            'login_type', 'customer'
        );
    END IF;

    SET val = test_employee_login(in_username,in_password);
    IF val=1 THEN 
        RETURN json_object(
            'success', 1=1,
            'login', in_username,
            'login_type', 'employee'
        );
    END IF;

    SET val = test_translator_login(in_username,in_password);
    IF val=1 THEN 
        RETURN json_object(
            'success', 1=1,
            'login', in_username,
            'login_type', 'employee'
        );
    END IF;

    RETURN json_object( 'success', 1=0);

END //