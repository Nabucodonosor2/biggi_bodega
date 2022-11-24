---------------------------- spdw_inf_ventas_mes_resumen ---------------------------
alter procedure spdw_inf_ventas_mes_resumen(@ve_ano		numeric
											,@ve_mes_desde	numeric
											,@ve_mes_hasta	numeric)
AS
BEGIN

declare
	@nv_confirmada					numeric
	,@nv_x_confirmar				numeric
	,@cant_nv						numeric	
	,@subtotal						numeric
	,@total_neto					numeric
	,@monto_dscto_corporativo		numeric
	,@despachado_neto				numeric
	,@cobrado_neto					numeric
	,@por_cobrar_neto				numeric
	,@cod_estado_nota_venta			numeric

	,@sum_subtotal						numeric
	,@sum_total_neto					numeric
	,@sum_total_venta					numeric
	,@sum_monto_dscto					numeric
	,@sum_monto_dscto_corporativo		numeric
	,@sum_monto_dscto_total				numeric
	,@sum_despachado_neto				numeric
	,@sum_cobrado_neto					numeric
	,@sum_por_cobrar_neto				numeric
	,@porc_dscto						numeric(10, 1)
	,@porc_dscto_corporativo			numeric(10, 1)
	,@porc_dscto_total					numeric(10, 1)


DECLARE C_NV CURSOR FOR  
select COD_ESTADO_NOTA_VENTA 
		,SUBTOTAL
		,TOTAL_NETO
		,isnull(dbo.f_nv_get_resultado(COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO'), 0) MONTO_DSCTO_CORPORATIVO
		,isnull(dbo.f_nv_despachado_neto(COD_NOTA_VENTA), 0) DESPACHADO_NETO
		,isnull(dbo.f_nv_cobrado_neto(COD_NOTA_VENTA), 0) COBRADO_NETO
		,isnull(dbo.f_nv_por_cobrar_neto(COD_NOTA_VENTA), 0) POR_COBRAR_NETO
from nota_venta
where COD_ESTADO_NOTA_VENTA <> 3 -- nula
  and year(fecha_nota_venta) = @ve_ano
  and month(fecha_nota_venta) between @ve_mes_desde and @ve_mes_hasta

set @nv_confirmada = 0
set @nv_x_confirmar = 0
set @cant_nv = 0
set @sum_subtotal = 0
set @sum_total_neto = 0
set @sum_monto_dscto_corporativo = 0
set @sum_despachado_neto = 0
set @sum_cobrado_neto = 0
set @sum_por_cobrar_neto = 0

OPEN C_NV
FETCH C_NV INTO @cod_estado_nota_venta, @subtotal, @total_neto, @monto_dscto_corporativo, @despachado_neto, @cobrado_neto, @por_cobrar_neto
WHILE @@FETCH_STATUS = 0 BEGIN	
	if (@cod_estado_nota_venta = 2 or @cod_estado_nota_venta = 4)
		set @nv_confirmada = @nv_confirmada + 1
	else if (@cod_estado_nota_venta = 1)
		set @nv_x_confirmar = @nv_x_confirmar + 1
	set @sum_subtotal = @sum_subtotal + @subtotal
	set @sum_total_neto = @sum_total_neto + @total_neto
	set @sum_monto_dscto_corporativo = @sum_monto_dscto_corporativo + @monto_dscto_corporativo
	set @sum_despachado_neto = @sum_despachado_neto + @despachado_neto
	set @sum_cobrado_neto = @sum_cobrado_neto + @cobrado_neto
	set @sum_por_cobrar_neto = @sum_por_cobrar_neto + @por_cobrar_neto

	FETCH C_NV INTO @cod_estado_nota_venta, @subtotal, @total_neto, @monto_dscto_corporativo, @despachado_neto, @cobrado_neto, @por_cobrar_neto
END
CLOSE C_NV
DEALLOCATE C_NV

set @cant_nv = @nv_confirmada + @nv_x_confirmar
set @sum_total_venta = @sum_total_neto - @sum_monto_dscto_corporativo
set @sum_monto_dscto = @sum_subtotal - @sum_total_neto
set @sum_monto_dscto_total = @sum_monto_dscto + @sum_monto_dscto_corporativo

set @porc_dscto = round(@sum_monto_dscto * 100/ @sum_subtotal, 1)
set @porc_dscto_corporativo = round(@sum_monto_dscto_corporativo  * 100/ @sum_subtotal, 1)
set @porc_dscto_total = round(@sum_monto_dscto_total  * 100/ @sum_subtotal, 1)

select @nv_confirmada					NV_CONFIRMADA
		,@nv_x_confirmar				NV_X_CONFIRMAR
		,@cant_nv						CANT_NV
		,@sum_subtotal					SUBTOTAL
		,@sum_total_neto				TOTAL_NETO
		,@sum_total_venta				TOTAL_VENTA	
		,@sum_monto_dscto				MONTO_DSCTO
		,@porc_dscto					PORC_DSCTO
		,@sum_monto_dscto_corporativo	MONTO_DSCTO_CORPORATIVO
		,@porc_dscto_corporativo		PORC_DSCTO_CORPORATIVO
		,@sum_monto_dscto_total			MONTO_DSCTO_TOTAL
		,@porc_dscto_total				PORC_DSCTO_TOTAL
		,@sum_despachado_neto			DESPACHADO_NETO
		,@sum_cobrado_neto				COBRADO_NETO
		,@sum_por_cobrar_neto			POR_COBRAR_NETO
END

