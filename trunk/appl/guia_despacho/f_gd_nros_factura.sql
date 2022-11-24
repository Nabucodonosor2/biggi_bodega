CREATE FUNCTION [dbo].[f_gd_nros_factura] (@ve_cod_gd numeric)
RETURNS varchar(100) 
AS
BEGIN
declare @var varchar(100),
		@nueva_var varchar(8000)

	set @nueva_var = ''
declare C_GUIAS cursor for
	SELECT	F.NRO_FACTURA
	FROM	GUIA_DESPACHO_FACTURA GDFA, FACTURA F
	WHERE	GDFA.COD_GUIA_DESPACHO = @ve_cod_gd
			AND F.COD_FACTURA = GDFA.COD_FACTURA
	order by F.COD_FACTURA

	OPEN	C_GUIAS
	FETCH	C_GUIAS INTO @var
	WHILE	@@FETCH_STATUS = 0 BEGIN		
		set @nueva_var = @nueva_var + @var+' - ';
	FETCH C_GUIAS INTO @var
	END
		set @nueva_var = substring(@nueva_var, 0, len(@nueva_var)) 
	CLOSE C_GUIAS
	DEALLOCATE C_GUIAS

return @nueva_var;
END