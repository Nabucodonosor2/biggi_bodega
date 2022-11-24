--------------------f_emp_get_mail_cargo_persona ---------------------
CREATE FUNCTION f_emp_get_mail_cargo_persona(@ve_cod_persona numeric,  @ve_formato varchar(2000))
RETURNS VARCHAR(2000)
AS
BEGIN
DECLARE @nom_cargo	VARCHAR(100),
		@mail		VARCHAR(100),
		@ciudad		VARCHAR(100),
		@resultado	varchar(2000)
 
		SELECT @mail = P.EMAIL,
				@nom_cargo = C.NOM_CARGO
	 	FROM PERSONA P, CARGO C
		WHERE P.COD_PERSONA = @ve_cod_persona
			and C.COD_CARGO = P.COD_CARGO

		if (@mail is null) set @mail = ''
		if (@nom_cargo is null) set @nom_cargo = ''

		set @resultado = @ve_formato
		set @resultado = replace(@resultado, '[EMAIL]', @mail)
		set @resultado = replace(@resultado, '[NOM_CARGO]', @nom_cargo)

		RETURN @resultado;

END
go
