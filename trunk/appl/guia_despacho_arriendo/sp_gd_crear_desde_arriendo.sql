-------------------------------- sp_gd_crear_desde_arriendo --------------------
alter PROCEDURE sp_gd_crear_desde_arriendo(@ve_cod_arriendo numeric, @ve_cod_usuario numeric)
AS
BEGIN  
	declare 
		@K_BODEGA_RENTAL			numeric
		,@K_DESPACHO_RENTAL			numeric
		,@vl_cod_guia_despacho		numeric
		,@vl_cod_empresa			numeric
		,@vl_cod_sucursal_despacho	numeric
		,@vl_cod_persona			numeric
		,@vl_referencia				varchar(100)
		,@vl_nro_orden_compra		varchar(20)
		,@vl_cod_item_arriendo		numeric

	set @K_BODEGA_RENTAL = 1
	set @K_DESPACHO_RENTAL = 5

	--	obtiene los datos del ARRIENDO
	select @vl_cod_empresa = cod_empresa
	      ,@vl_cod_sucursal_despacho = cod_sucursal
	      ,@vl_cod_persona = cod_persona
	      ,@vl_referencia = referencia
	      ,@vl_nro_orden_compra = nro_orden_compra
	from arriendo
	where cod_arriendo = @ve_cod_arriendo
	-- crea la GD
	execute spu_guia_despacho 
		'INSERT' 
		,NULL -- cod_guia_despacho = identity
		,NULL -- cod_usuario_impresion
		,@ve_cod_usuario 
		,NULL -- nro_guia_despacho		
		,1 -- cod_estado_doc_sii = emitida
		,@vl_cod_empresa 
		,@vl_cod_sucursal_despacho
		,@vl_cod_persona
		,@vl_referencia 
		,@vl_nro_orden_compra
		,NULL -- obs
		,NULL -- retirado_por
		,NULL -- rut_retirado_por
		,NULL -- dig_verif_retirado_por
		,NULL -- guia_transporte
		,NULL -- patente
		,NULL -- cod_factura
		,@K_BODEGA_RENTAL -- cod_bodega RENTAL
		,@K_DESPACHO_RENTAL -- cod_tipo_guia_despacho = arriendo
		,@ve_cod_arriendo 
		,NULL -- motivo_anula
		,NULL -- cod_usuario_anula 		 

	set @vl_cod_guia_despacho = @@identity

	declare C_ITEM cursor for 
	select cod_item_arriendo
	from item_arriendo
	where cod_arriendo = @ve_cod_arriendo 
	  and dbo.f_arr_cant_por_despachar(cod_item_arriendo, 'TODO_ESTADO') > 0
		
	open C_ITEM 
	fetch C_ITEM into @vl_cod_item_arriendo
	while @@fetch_status = 0 begin
		insert into item_guia_despacho(
			cod_guia_despacho,
			orden,
			item,
			cod_producto,
			nom_producto,
			cantidad,
			precio,
			cod_item_doc,
			tipo_doc)
		select @vl_cod_guia_despacho,
			orden,
			item,
			cod_producto,
			nom_producto,
			case 
				when dbo.f_arr_cant_por_despachar(cod_item_arriendo, 'TODO_ESTADO') <= dbo.f_bodega_stock_cero(COD_PRODUCTO, @K_BODEGA_RENTAL, getdate()) 
					then dbo.f_arr_cant_por_despachar(cod_item_arriendo, 'TODO_ESTADO')
				else
					dbo.f_bodega_stock_cero(COD_PRODUCTO, @K_BODEGA_RENTAL, getdate()) 
			end,	-- cantidad (el minimo entre ambos valores
			precio_venta,
			@vl_cod_item_arriendo,
			'ITEM_ARRIENDO'
		from item_arriendo
		where cod_item_arriendo = @vl_cod_item_arriendo

		fetch C_ITEM into @vl_cod_item_arriendo
	end
	close C_ITEM
	deallocate C_ITEM
END