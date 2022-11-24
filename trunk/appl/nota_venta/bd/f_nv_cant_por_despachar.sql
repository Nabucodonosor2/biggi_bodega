alter FUNCTION [dbo].[f_nv_cant_por_despachar](@ve_cod_item_nota_venta numeric, @ve_filtro varchar(20)=NULL)
RETURNS T_CANTIDAD 
AS
BEGIN

declare @total_nv 				T_CANTIDAD,
		@res 					T_CANTIDAD,	
		@total_despachada_gd	T_CANTIDAD,
		@total_despachada_fa	T_CANTIDAD,
		@total_despachada		T_CANTIDAD,
		@es_despachable			varchar(1),
		@vl_cod_nota_venta		numeric,
		@total_gr_desde_gd		T_CANTIDAD,
		@total_gr_desde_fa		T_CANTIDAD,
		@total_gr				T_CANTIDAD,
		@kl_estado_nota_venta_anulada numeric,
		@vl_estado_nota_venta numeric,
		@cod_item_guia_despacho numeric,
		@cod_item_factura numeric,
		@kl_tipo_gr_garantia numeric,
		@total_despachada_ajuste numeric(10,2),
		@vl_precio					numeric,
		@vl_cod_producto			varchar(100)
		
	set @kl_estado_nota_venta_anulada = 3
	set @kl_tipo_gr_garantia = 2

	select @vl_cod_nota_venta = cod_nota_venta from item_nota_venta where cod_item_nota_venta = @ve_cod_item_nota_venta
	if (@vl_cod_nota_venta < 52000)
		return 0

	if (@vl_cod_nota_venta in (52022,52024,52026,52041,52048,52069,52071,52083,5210552124,52163,52164,52183,52198,52199,52252,52273,52105))
		return 0

	select @vl_estado_nota_venta = cod_estado_nota_venta from nota_venta where cod_nota_venta = @vl_cod_nota_venta
	if (@vl_estado_nota_venta = @kl_estado_nota_venta_anulada)
		return 0

	select @total_nv = it.cantidad
			,@es_despachable = p.es_despachable
			,@vl_precio = it.precio
			,@vl_cod_producto = it.cod_producto
	from item_nota_venta it, producto p
	where it.cod_item_nota_venta = @ve_cod_item_nota_venta and
		  p.cod_producto = it.cod_producto
	
	if (@es_despachable='N')
		return 0
	
	-- Para F, E, I, VT si el precio es cero no se considera despachable
	if (@vl_cod_producto in  ('F','E','I','VT') and @vl_precio=0)
		return 0

	--recorre items gr desde gd
	declare c_cursor_gd cursor for 
	select cod_item_guia_despacho from item_guia_despacho it, guia_despacho gd
	where it.cod_item_doc = @ve_cod_item_nota_venta and
		it.tipo_doc = 'ITEM_NOTA_VENTA' and
		gd.cod_guia_despacho = it.cod_guia_despacho and
		((gd.cod_estado_doc_sii in (2, 3)) or ( gd.cod_estado_doc_sii =1 and @ve_filtro = 'TODO_ESTADO'))
			

	set @total_gr_desde_gd = 0
	open c_cursor_gd 
	fetch c_cursor_gd into @cod_item_guia_despacho
	while @@fetch_status = 0 
	begin
		select @total_gr_desde_gd = @total_gr_desde_gd + isnull(sum(cantidad), 0) from item_guia_recepcion it, guia_recepcion gr
		where it.cod_item_doc = @cod_item_guia_despacho and
			it.tipo_doc = 'ITEM_GUIA_DESPACHO' and
			gr.cod_guia_recepcion = it.cod_guia_recepcion and
			gr.cod_estado_guia_recepcion in (1,2) and
			gr.cod_tipo_guia_recepcion = @kl_tipo_gr_garantia

	fetch c_cursor_gd into @cod_item_guia_despacho
	end
	close c_cursor_gd
	deallocate c_cursor_gd

	--recorre items gr desde fa
	declare c_cursor_fa cursor for 
	select cod_item_factura from item_factura it, factura f
	where it.cod_item_doc = @ve_cod_item_nota_venta and
		it.tipo_doc = 'ITEM_NOTA_VENTA' and
		f.cod_factura = it.cod_factura and
		((f.cod_estado_doc_sii in (2, 3)) or ( f.cod_estado_doc_sii =1 and @ve_filtro = 'TODO_ESTADO'))
	
	set @total_gr_desde_fa = 0
	open c_cursor_fa 
	fetch c_cursor_fa into @cod_item_factura
	while @@fetch_status = 0 
	begin
		select @total_gr_desde_fa = @total_gr_desde_fa + isnull(sum(cantidad), 0) from item_guia_recepcion it, guia_recepcion gr
		where it.cod_item_doc = @cod_item_factura and
			it.tipo_doc = 'ITEM_FACTURA' and
			gr.cod_guia_recepcion = it.cod_guia_recepcion and
			gr.cod_estado_guia_recepcion in (1,2) and
			gr.cod_tipo_guia_recepcion = @kl_tipo_gr_garantia

	fetch c_cursor_fa into @cod_item_factura
	end
	close c_cursor_fa
	deallocate c_cursor_fa

	--total gr
	set @total_gr = @total_gr_desde_gd + @total_gr_desde_fa
	--total entregado
	set @total_nv = @total_nv + @total_gr	

	--total despachado
	select @total_despachada_gd = isnull(sum(cantidad), 0) 
	from item_guia_despacho igd, guia_despacho gd 
	where igd.cod_item_doc = @ve_cod_item_nota_venta and
		  igd.tipo_doc = 'ITEM_NOTA_VENTA' and
			igd.cod_guia_despacho = gd.cod_guia_despacho and
		((gd.cod_estado_doc_sii in (2, 3)) or ( gd.cod_estado_doc_sii =1 and @ve_filtro = 'TODO_ESTADO'))

	select @total_despachada_fa = isnull(sum(cantidad), 0) 
	from item_factura i, factura f
	where i.cod_item_doc = @ve_cod_item_nota_venta and
		  i.tipo_doc = 'ITEM_NOTA_VENTA' and
		  i.cod_factura = f.cod_factura and
		  f.genera_salida = 'S' and
		((f.cod_estado_doc_sii in (2, 3)) or ( f.cod_estado_doc_sii =1 and @ve_filtro = 'TODO_ESTADO'))
	
	--total despachado ajuste
	select @total_despachada_ajuste = isnull(sum(cantidad), 0)
	from ajuste_despachar_nota_venta
	where cod_item_nota_venta = @ve_cod_item_nota_venta

	set @total_despachada = @total_despachada_gd + @total_despachada_fa + @total_despachada_ajuste

	if (@total_nv <= @total_despachada)
		set @res = 0
	else
		set @res = @total_nv - @total_despachada
		
	return @res;
	
END
go