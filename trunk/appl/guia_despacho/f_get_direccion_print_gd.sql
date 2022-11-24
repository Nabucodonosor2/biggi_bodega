set ANSI_NULLS ON
set QUOTED_IDENTIFIER ON
GO
CREATE FUNCTION [dbo].[f_get_direccion_print_gd](@ve_cod_empresa numeric, @ve_formato varchar(2000))
RETURNS VARCHAR(2000)
AS
BEGIN
	DECLARE @direccion	VARCHAR(100),
			@comuna		VARCHAR(100),
			@ciudad		VARCHAR(100),
			@resultado	VARCHAR(2000)
			
	SELECT @direccion = S.DIRECCION,
		@comuna = C.NOM_COMUNA,
		@ciudad = CI.NOM_CIUDAD
	 FROM SUCURSAL S left outer join COMUNA C on S.COD_COMUNA = C.COD_COMUNA, CIUDAD CI 
	 WHERE S.DIRECCION_FACTURA = 'S' AND
		S.COD_EMPRESA = @ve_cod_empresa	AND
		S.COD_CIUDAD = CI.COD_CIUDAD

	if (@direccion is null) set @direccion = ''
	if (@comuna is null) set @comuna = ''
	if (@ciudad is null) set @ciudad = ''

	set @resultado = @ve_formato
	set @resultado = replace(@resultado, '[DIRECCION]', @direccion)
	set @resultado = replace(@resultado, '[NOM_COMUNA]', @comuna)
	set @resultado = replace(@resultado, '[NOM_CIUDAD]', @ciudad)

	RETURN @resultado;


END

