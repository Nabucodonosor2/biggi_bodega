------------------------- f_mant_estado_solucion ---------------
alter FUNCTION f_mant_estado_solucion(@ve_cod_mantencion_sw numeric)
RETURNS numeric
AS
BEGIN
	declare 
		@cod_solucion_sw		numeric
		,@cod_estado_solucion	numeric

	select @cod_solucion_sw = max(cod_solucion_sw)
	from solucion_sw
	where cod_mantencion_sw = @ve_cod_mantencion_sw

	select @cod_estado_solucion = cod_estado_solucion_sw
	from solucion_sw
	where cod_solucion_sw = @cod_solucion_sw

	return @cod_estado_solucion
END
