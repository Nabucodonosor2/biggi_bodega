create FUNCTION f_salida_fecha_doc(@ve_cod_salida_bodega numeric)
RETURNS datetime
AS
BEGIN
	declare
		@vl_tipo_doc		varchar(100)
		,@vl_fecha_doc		datetime

	set @vl_fecha_doc = null

	select @vl_tipo_doc	= tipo_doc		
	from salida_bodega 
	where cod_salida_bodega = @ve_cod_salida_bodega
	
	if (@vl_tipo_doc = 'FACTURA') begin
		select @vl_fecha_doc = f.fecha_factura
		from salida_bodega s, factura f
		where s.cod_salida_bodega = @ve_cod_salida_bodega
		  and f.cod_factura = s.cod_doc
		  and f.cod_tipo_factura = 3 -- desde OC COmercial
	end
	return @vl_fecha_doc 
END

