create FUNCTION f_salida_NV_COMERCIAL(@ve_cod_salida_bodega numeric)
RETURNS numeric
AS
BEGIN
	declare
		@vl_tipo_doc		varchar(100)
		,@vl_cod_orden_compra	numeric
		,@vl_cod_nota_venta	numeric

	set @vl_cod_nota_venta = null

	select @vl_tipo_doc	= tipo_doc		
	from salida_bodega 
	where cod_salida_bodega = @ve_cod_salida_bodega
	
	if (@vl_tipo_doc = 'FACTURA') begin
		select @vl_cod_orden_compra = f.cod_doc
		from salida_bodega s, factura f
		where s.cod_salida_bodega = @ve_cod_salida_bodega
		  and f.cod_factura = s.cod_doc
		  and f.cod_tipo_factura = 3 -- desde OC COmercial

		if (@vl_cod_orden_compra is not null) begin
			select @vl_cod_nota_venta = N.cod_nota_venta
			from BIGGI.dbo.ORDEN_COMPRA O, BIGGI.dbo.NOTA_VENTA N, BIGGI.dbo.USUARIO U
			where O.COD_ORDEN_COMPRA = @vl_cod_orden_compra
			  and N.COD_NOTA_VENTA = O.COD_NOTA_VENTA
			  and U.COD_USUARIO = N.COD_USUARIO_VENDEDOR1
		end
	end
	return @vl_cod_nota_venta 
END

