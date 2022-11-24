create FUNCTION f_fa_NV_COMERCIAL(@ve_cod_factura numeric)
RETURNS numeric
AS
BEGIN
	declare
		@vl_tipo_doc			varchar(100)
		,@vl_cod_orden_compra	numeric
		,@vl_cod_nota_venta		numeric
		,@vl_cod_tipo_factura	numeric

	set @vl_cod_nota_venta = null

	select @vl_cod_orden_compra = f.cod_doc
			,@vl_cod_tipo_factura = f.cod_tipo_factura
	from factura f
	where f.cod_factura = @ve_cod_factura

	if (@vl_cod_tipo_factura = 3) begin -- desde OC COmercial
		if (@vl_cod_orden_compra is not null) begin
			select @vl_cod_nota_venta = cod_nota_venta
			from BIGGI.dbo.ORDEN_COMPRA O
			where O.COD_ORDEN_COMPRA = @vl_cod_orden_compra
		end
	end
	return @vl_cod_nota_venta 
END

