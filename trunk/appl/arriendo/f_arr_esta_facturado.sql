--------------------  f_arr_esta_facturado  ----------------
alter FUNCTION f_arr_esta_facturado(@ve_cod_arriendo	numeric
									,@ve_fecha			datetime)
RETURNS numeric
AS
/*
retorna 1 si el arriendo ya esta en alguna factura para el mes y año de la ve_fecha
0 en otro caso
*/
BEGIN
declare
	@vl_count	numeric

	select @vl_count = count(*)
	from factura f, item_factura i, item_arriendo ia
	where month(isnull(f.fecha_factura, f.fecha_registro)) = month(@ve_fecha)
	  and year(isnull(f.fecha_factura, f.fecha_registro)) = year(@ve_fecha)
	  and f.cod_estado_doc_sii in (1, 2,3)
	  and f.cod_tipo_factura = 2	-- arriendo
	  and i.cod_factura = f.cod_factura
	  and ia.cod_item_arriendo = i.cod_item_doc
	  and ia.cod_arriendo = @ve_cod_arriendo

	if (@vl_count > 0)
		return 1

	return 0
END
