-- creada por Iván Sánchez 25/05/09
CREATE FUNCTION [dbo].[f_nv_costo_unitario](@ve_cod_item_nota_venta numeric, @ve_cod_producto varchar(30))
RETURNS T_PRECIO
AS
BEGIN
DECLARE @costo_ioc	numeric,
		@costo_poc numeric,
		@costo_pp numeric,
		@costo_unitario numeric

		-- item_orden_compra
		select @costo_ioc = isnull(PRECIO, 0)
		from ITEM_ORDEN_COMPRA
		where COD_ITEM_NOTA_VENTA = @ve_cod_item_nota_venta
		
		-- pre_orden_compra 
		select @costo_poc = isnull(PRECIO_COMPRA, 0)
		from PRE_ORDEN_COMPRA
		where COD_ITEM_NOTA_VENTA = @ve_cod_item_nota_venta
		
		-- producto_proveedor
		select  @costo_pp =  isnull(dbo.f_prod_get_precio_costo (COD_PRODUCTO, dbo.f_nv_get_first_proveedor (COD_PRODUCTO), getdate()), 0)
		from PRODUCTO_PROVEEDOR
		where COD_PRODUCTO = @ve_cod_producto
		
		if (@costo_ioc <> 0)
			set @costo_unitario = @costo_ioc
		else if (@costo_poc <> 0)
			set @costo_unitario = @costo_poc	
		else if (@costo_pp <> 0)
			set @costo_unitario = @costo_pp 

	return @costo_unitario	   
END
go
