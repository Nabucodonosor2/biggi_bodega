CREATE FUNCTION [dbo].[f_fa_nros_guia_despacho] (@ve_cod_factura numeric)
RETURNS varchar(100) 
AS
BEGIN
declare @var varchar(100)
		, @nueva_var varchar(8000)

	set @nueva_var = ''
declare C_GUIAS cursor for
	SELECT	GD.NRO_GUIA_DESPACHO
	FROM	GUIA_DESPACHO_FACTURA GDFA, GUIA_DESPACHO GD
	WHERE	GDFA.COD_FACTURA = @VE_COD_FACTURA
			AND GD.COD_GUIA_DESPACHO = GDFA.COD_GUIA_DESPACHO
	order by GD.NRO_GUIA_DESPACHO

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