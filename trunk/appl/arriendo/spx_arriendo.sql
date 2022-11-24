-------------------- spx_arriendo ---------------------------------
alter PROCEDURE spx_arriendo
AS
BEGIN
	declare
		@K_BODEGA_ARRIENDO		numeric
		,@K_DESCONTINUADO		numeric
		,@vc_cod_arriendo		numeric
		,@vc_cod_empresa		numeric
		,@vl_cod_sucursal		numeric
		,@vl_cod_persona		numeric
		,@vl_nom_bodega			varchar(100)
		,@vl_cod_bodega			numeric
		,@vl_cod_entrada_bodega		numeric
		,@vc_cod_item_arriendo		numeric
		,@vc_cod_producto			varchar(30)
		,@vc_nom_producto			varchar(100)
		,@vl_orden					numeric
		,@vl_cod_producto_TE		varchar(30)

	set @K_BODEGA_ARRIENDO = 2
	set @K_DESCONTINUADO = 4

	declare C_ARR CURSOR FOR  
	select cod_arriendo
			,cod_empresa
	from  arriendo

	OPEN C_ARR
	FETCH C_ARR INTO @vc_cod_arriendo, @vc_cod_empresa
	WHILE @@FETCH_STATUS = 0 BEGIN	
		-- llena la sucursal y persona del arriendo
		select top 1 @vl_cod_sucursal = cod_sucursal
		from sucursal
		where cod_empresa = @vc_cod_empresa
		  and direccion_factura = 'S'

		select top 1 @vl_cod_persona = p.cod_persona
		from sucursal s, persona p
		where s.cod_empresa = @vc_cod_empresa
		  and p.cod_sucursal = s.cod_sucursal
		order by s.cod_sucursal , p.cod_persona

		update arriendo
		set cod_sucursal = @vl_cod_sucursal
			,cod_persona = @vl_cod_persona
		where cod_arriendo = @vc_cod_arriendo

		-- asigna el campo orden a los item_arriendo, crea los productois TE
		declare C_ITEM CURSOR FOR  
		select cod_item_arriendo
				,cod_producto
				,nom_producto
		from  item_arriendo
		where cod_arriendo = @vc_cod_arriendo
		order by orden, item

		set @vl_orden = 10
		OPEN C_ITEM
		FETCH C_ITEM INTO @vc_cod_item_arriendo, @vc_cod_producto, @vc_nom_producto
		WHILE @@FETCH_STATUS = 0 BEGIN	
			update item_arriendo 
			set orden = @vl_orden
			where cod_item_arriendo = @vc_cod_item_arriendo

			set @vl_orden = @vl_orden + 10

			if (@vc_cod_producto = 'TE') begin		
				exec sp_arr_crear_producto_TE 	@vc_cod_item_arriendo
			end

			FETCH C_ITEM INTO @vc_cod_item_arriendo, @vc_cod_producto, @vc_nom_producto
		END
		CLOSE C_ITEM
		DEALLOCATE C_ITEM		


		-- crea bodega
		set @vl_nom_bodega = 'Rental contrato ' + convert(varchar, @vc_cod_arriendo)
		exec spu_bodega 'INSERT', null, @vl_nom_bodega, @K_BODEGA_ARRIENDO
		set @vl_cod_bodega = @@identity

		update ARRIENDO
		set COD_BODEGA = @vl_cod_bodega
		where COD_ARRIENDO = @vc_cod_arriendo

		-- crea una entrada por cada contrato
		exec spu_entrada_bodega 'INSERT', null,  1, @vl_cod_bodega, 'INICIAL', null
		set @vl_cod_entrada_bodega = @@identity
		insert into item_entrada_bodega
			(cod_entrada_bodega
			,orden
			,item
			,cod_producto
			,nom_producto
			,cantidad
			,precio)
		select @vl_cod_entrada_bodega
			,orden
			,isnull(item, '')
			,cod_producto
			,nom_producto
			,cantidad
			,precio
		from item_arriendo
		where COD_ARRIENDO = @vc_cod_arriendo
		  and cod_producto <> 'TE'	--***************
		order by orden
		
		FETCH C_ARR INTO @vc_cod_arriendo, @vc_cod_empresa
	END
	CLOSE C_ARR
	DEALLOCATE C_ARR		
END
go