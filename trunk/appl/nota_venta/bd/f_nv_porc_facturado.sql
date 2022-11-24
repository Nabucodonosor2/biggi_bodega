----------------------- f_nv_porc_facturado ------------------------
ALTER FUNCTION [dbo].[f_nv_porc_facturado](@ve_cod_nota_venta numeric)
RETURNS T_PORCENTAJE
AS
BEGIN

declare @nv_total_neto			T_PRECIO,
		@fa_total_neto			T_PRECIO,
		@fa_desde_gd_neto		T_PRECIO,
		@nc_desde_fa_neto		T_PRECIO,		
		@porc_facturado			T_PORCENTAJE,
		@K_ESTADO_IMPRESA		numeric,
		@K_ESTADO_ENVIADA		numeric,
		@fa_total_ajuste		numeric (14,2)

	set @K_ESTADO_IMPRESA = 2
	set @K_ESTADO_ENVIADA = 3

	-- total cantidad NV
	select @nv_total_neto = total_neto
	from nota_venta 
	where cod_nota_venta = @ve_cod_nota_venta

	if (@nv_total_neto=0)
		return 0

	-- total facturado desde NV
	select @fa_total_neto = isnull(sum(total_neto), 0)
	from factura
	where cod_estado_doc_sii in (@K_ESTADO_IMPRESA, @K_ESTADO_ENVIADA)
	  and tipo_doc = 'NOTA_VENTA'
	  AND cod_doc = @ve_cod_nota_venta
		
	-- total facturado desde GD
	select @fa_desde_gd_neto = isnull(sum(total_neto), 0)
	from factura
	where cod_estado_doc_sii in (@K_ESTADO_IMPRESA, @K_ESTADO_ENVIADA)
	  and tipo_doc = 'GUIA_DESPACHO'
	  AND cod_doc = @ve_cod_nota_venta
	
	-- total NC de la NV
	select @nc_desde_fa_neto = isnull(sum(total_neto), 0)
	from nota_credito
	where cod_estado_doc_sii in (@K_ESTADO_IMPRESA, @K_ESTADO_ENVIADA)
		and cod_motivo_nota_credito <> 1 -- DEVOLUCION
		and cod_doc in (select cod_factura from factura 
			where cod_doc = @ve_cod_nota_venta 
				and (tipo_doc = 'NOTA_VENTA' or tipo_doc = 'GUIA_DESPACHO') 
				and cod_estado_doc_sii in (@K_ESTADO_IMPRESA, @K_ESTADO_ENVIADA))

	--total FA ajustado
	select @fa_total_ajuste = isnull(sum(monto), 0)
	from ajuste_facturar_nota_venta
	where cod_nota_venta = @ve_cod_nota_venta 
				
	set @porc_facturado = round((@fa_total_neto + @fa_total_ajuste +@fa_desde_gd_neto - @nc_desde_fa_neto)/@nv_total_neto * 100, 1)

return @porc_facturado
END
go