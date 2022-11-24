------------------f_prod_get_precio_costo----------------
-- Esta función retorna el precio costo vigemte de un producto de un determinado proveedor.

alter FUNCTION [dbo].[f_prod_get_precio_costo](@ve_cod_producto varchar(30)
												,@ve_cod_empresa numeric
												,@ve_fecha_inicio_vigencia datetime)
RETURNS numeric
AS
BEGIN
DECLARE @precio	numeric
	set @precio = 0
	select		top (1)  @precio = (isnull(PRECIO ,0))
	from		PRODUCTO_PROVEEDOR PP, COSTO_PRODUCTO CP
	where		PP.COD_PRODUCTO	= @ve_cod_producto
				and PP.COD_EMPRESA	= @ve_cod_empresa
				and CP.COD_PRODUCTO_PROVEEDOR = PP.COD_PRODUCTO_PROVEEDOR
				and	CP.FECHA_INICIO_VIGENCIA <= @ve_fecha_inicio_vigencia
				and PP.ELIMINADO = 'N'				
	order by	CP.FECHA_INICIO_VIGENCIA desc
	
	if (@@rowcount=0)	-- no existe precio para este proveedor, usa el precio para PRIMER proveedor
		select		top (1)  @precio = (isnull(PRECIO ,0))
		from		PRODUCTO_PROVEEDOR PP, COSTO_PRODUCTO CP
		where		PP.COD_PRODUCTO	= @ve_cod_producto
					and PP.COD_EMPRESA	= dbo.f_nv_get_first_proveedor(@ve_cod_producto)
					and CP.COD_PRODUCTO_PROVEEDOR = PP.COD_PRODUCTO_PROVEEDOR
					and	CP.FECHA_INICIO_VIGENCIA <= @ve_fecha_inicio_vigencia
					and PP.ELIMINADO = 'N'				
		order by	CP.FECHA_INICIO_VIGENCIA desc
	
	RETURN @precio;
END
go