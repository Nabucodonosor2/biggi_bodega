CREATE FUNCTION [dbo].[f_nv_cant_por_facturar](@ve_cod_item_nota_venta numeric, @ve_filtro varchar(20)=NULL)
RETURNS T_CANTIDAD 
AS
BEGIN

declare @total				T_CANTIDAD,
		@res				T_CANTIDAD,	
		@total_facturada	T_CANTIDAD,
		@total_facturada_gd	T_CANTIDAD,
		@total_facturada_nv	T_CANTIDAD,
		@total_nc1			T_CANTIDAD,
		@total_nc2			T_CANTIDAD,
		@K_FACTURA			numeric,
		@K_ESTADO_EMITIDA	numeric,
		@K_ESTADO_IMPRESA	numeric,
		@K_ESTADO_ENVIADA	numeric,
		@K_ESTADO_ANULADA	numeric

	set @K_FACTURA = 1
	set @K_ESTADO_EMITIDA = 1
	set @K_ESTADO_IMPRESA = 2
	set @K_ESTADO_ENVIADA = 3
	set @K_ESTADO_ANULADA = 4
	
	-- total cantidad NV
	select @total = isnull(sum(cantidad), 0) 
	from item_nota_venta 
	where cod_item_nota_venta = @ve_cod_item_nota_venta
		
	-- cantidad facturada desde NV
	select @total_facturada_nv = isnull(sum(it_f.cantidad), 0)
	from item_factura it_f , factura f
	where cod_item_doc = @ve_cod_item_nota_venta and
			it_f.tipo_doc = 'ITEM_NOTA_VENTA' and
			it_f.cod_factura = f.cod_factura and  
			((f.cod_estado_doc_sii in ( @K_ESTADO_IMPRESA, @K_ESTADO_ENVIADA)) or ( f.cod_estado_doc_sii = @K_ESTADO_EMITIDA and @ve_filtro = 'TODO_ESTADO'))

	-- cantidad facturada desde GD
	select @total_facturada_gd = isnull(sum(it_f.cantidad), 0)
	from item_guia_despacho it_gd,item_factura it_f, factura f
	where it_gd.cod_item_doc = @ve_cod_item_nota_venta and
			it_gd.tipo_doc = 'ITEM_NOTA_VENTA' and
			it_f.cod_item_doc = it_gd.cod_item_guia_despacho and
			it_f.tipo_doc = 'ITEM_GUIA_DESPACHO' and
			f.cod_factura = it_f.cod_factura and
			((f.cod_estado_doc_sii in ( @K_ESTADO_IMPRESA, @K_ESTADO_ENVIADA)) or ( f.cod_estado_doc_sii = @K_ESTADO_EMITIDA and @ve_filtro = 'TODO_ESTADO'))

	---------------------------------------
	-- NC creadas desde FA que a su vez fueron creadas NV
	select @total_nc1 = isnull(sum(cantidad), 0)
	from item_nota_credito i , nota_credito nc
	where nc.COD_TIPO_NOTA_CREDITO =	@K_FACTURA	and
 		((nc.cod_estado_doc_sii in ( @K_ESTADO_IMPRESA, @K_ESTADO_ENVIADA)) or (nc.cod_estado_doc_sii = @K_ESTADO_EMITIDA and @ve_filtro = 'TODO_ESTADO')) and
		  i.cod_nota_credito = nc.cod_nota_credito and  
		  i.tipo_doc = 'ITEM_FACTURA' and
		  i.cod_item_doc in (select it_f.cod_item_factura
							 from item_factura it_f , factura f
							 where cod_item_doc = @ve_cod_item_nota_venta and
									it_f.tipo_doc = 'ITEM_NOTA_VENTA' and
									it_f.cod_factura = f.cod_factura and  
									((f.cod_estado_doc_sii in ( @K_ESTADO_IMPRESA, @K_ESTADO_ENVIADA)) or ( f.cod_estado_doc_sii = @K_ESTADO_EMITIDA and @ve_filtro = 'TODO_ESTADO')))

	-- NC creadas desde FA que a su vez son creadas desde GD
	select @total_nc2 = isnull(sum(cantidad), 0)
	from item_nota_credito i , nota_credito nc
	where nc.COD_TIPO_NOTA_CREDITO =	@K_FACTURA	and
 		((nc.cod_estado_doc_sii in ( @K_ESTADO_IMPRESA, @K_ESTADO_ENVIADA)) or (nc.cod_estado_doc_sii = @K_ESTADO_EMITIDA and @ve_filtro = 'TODO_ESTADO')) and
		  i.cod_nota_credito = nc.cod_nota_credito and  
		  i.tipo_doc = 'ITEM_FACTURA' and
		  i.cod_item_doc in (select it_f.cod_item_factura
							 from item_guia_despacho it_gd,item_factura it_f, factura f
							 where it_gd.cod_item_doc = @ve_cod_item_nota_venta and
									it_gd.tipo_doc = 'ITEM_NOTA_VENTA' and
									it_f.cod_item_doc = it_gd.cod_item_guia_despacho and
									it_f.tipo_doc = 'ITEM_GUIA_DESPACHO' and
									f.cod_factura = it_f.cod_factura and
									((f.cod_estado_doc_sii in ( @K_ESTADO_IMPRESA, @K_ESTADO_ENVIADA)) or ( f.cod_estado_doc_sii = @K_ESTADO_EMITIDA and @ve_filtro = 'TODO_ESTADO')))

	set @total_facturada = @total_facturada_gd + @total_facturada_nv - (@total_nc1 + @total_nc2)
	set @res = @total - @total_facturada
	
return @res;
END
go