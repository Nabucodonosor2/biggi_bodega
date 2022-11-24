alter FUNCTION f_salida_OC_COMERCIAL(@ve_cod_salida_bodega numeric)
RETURNS numeric
AS
BEGIN
	declare
		@vl_tipo_doc		varchar(100)
		,@vl_cod_orden_compra	numeric

	set @vl_cod_orden_compra = null

	select @vl_tipo_doc	= tipo_doc		
	from salida_bodega 
	where cod_salida_bodega = @ve_cod_salida_bodega
	
	if (@vl_tipo_doc = 'FACTURA') begin
		select @vl_cod_orden_compra = convert(numeric, f.nro_orden_compra)
		from salida_bodega s, factura f
		where s.cod_salida_bodega = @ve_cod_salida_bodega
		  and f.cod_factura = s.cod_doc
		  and f.cod_tipo_factura = 3 -- desde OC COmercial
	end
	return @vl_cod_orden_compra 
END

