-------------------- spu_mod_arriendo---------------------------------
alter PROCEDURE spu_mod_arriendo(@ve_operacion						varchar(20)
								,@ve_cod_mod_arriendo				numeric
								,@ve_cod_usuario					numeric=null
								,@ve_cod_arriendo					numeric=null
								,@ve_cod_estado_mod_arriendo		numeric=null
								,@ve_referencia						varchar(100)=null
								,@ve_subtotal						numeric=null
								,@ve_total_neto						numeric=null
								,@ve_porc_iva						T_PORCENTAJE=null
								,@ve_monto_iva						numeric=null
								,@ve_total_con_iva					numeric=null
								)
AS
BEGIN
	if (@ve_operacion='INSERT')
		insert into MOD_ARRIENDO
			(FECHA_MOD_ARRIENDO
			,COD_USUARIO
			,COD_ARRIENDO
			,COD_ESTADO_MOD_ARRIENDO
			,REFERENCIA
			,SUBTOTAL
			,TOTAL_NETO
			,PORC_IVA
			,MONTO_IVA
			,TOTAL_CON_IVA
			)
		values
			(getdate()							--FECHA_MOD_ARRIENDO
			,@ve_cod_usuario					--COD_USUARIO
			,@ve_cod_arriendo					--COD_ARRIENDO
			,@ve_cod_estado_mod_arriendo		--COD_ESTADO_MOD_ARRIENDO
			,@ve_referencia						--REFERENCIA
			,@ve_subtotal						--SUBTOTAL
			,@ve_total_neto						--TOTAL_NETO
			,@ve_porc_iva						--PORC_IVA
			,@ve_monto_iva						--MONTO_IVA
			,@ve_total_con_iva					--TOTAL_CON_IVA
			)
	else if (@ve_operacion='UPDATE') begin
		update MOD_ARRIENDO
		set COD_USUARIO = @ve_cod_usuario
			,COD_ARRIENDO = @ve_cod_arriendo
			,COD_ESTADO_MOD_ARRIENDO = @ve_cod_estado_mod_arriendo
			,REFERENCIA = @ve_referencia
			,SUBTOTAL = @ve_subtotal
			,TOTAL_NETO = @ve_total_neto
			,PORC_IVA = @ve_porc_iva
			,MONTO_IVA = @ve_monto_iva
			,TOTAL_CON_IVA = @ve_total_con_iva
		where COD_MOD_ARRIENDO = @ve_cod_mod_arriendo
	end
	else if (@ve_operacion='DELETE') begin
		delete ITEM_MOD_ARRIENDO 
		where COD_MOD_ARRIENDO = @ve_cod_mod_arriendo

		delete MOD_ARRIENDO 
		where COD_MOD_ARRIENDO = @ve_cod_mod_arriendo
	end		
	else if (@ve_operacion='APROBAR') begin
		-- obtiene el max ORDEN existente en ITEM_ARRIENDO
		declare
			@orden						numeric
			,@cod_arriendo				numeric
			,@vc_cod_item_arriendo		numeric
			,@vc_cod_item_mod_arriendo	numeric
			,@vl_cod_producto_TE		varchar(30)

		select @orden = max(i.orden)
		from mod_arriendo m, item_arriendo i
		where m.cod_mod_arriendo = @ve_cod_mod_arriendo
		  and i.cod_arriendo = m.cod_arriendo

		insert into ITEM_ARRIENDO
		    (COD_ARRIENDO
			,ORDEN
			,ITEM
			,COD_PRODUCTO
			,NOM_PRODUCTO
			,CANTIDAD
			,PRECIO
			,PRECIO_VENTA
			,COD_TIPO_TE
			,MOTIVO_TE
			,COD_ITEM_MOD_ARRIENDO
			)
		select M.COD_ARRIENDO
				,@orden  + I.ORDEN
				,I.ITEM
				,I.COD_PRODUCTO
				,I.NOM_PRODUCTO
				,I.CANTIDAD
				,I.PRECIO
				,I.PRECIO_VENTA
				,I.COD_TIPO_TE
				,I.MOTIVO_TE
				,I.COD_ITEM_MOD_ARRIENDO
		from  ITEM_MOD_ARRIENDO I, MOD_ARRIENDO M
		where I.COD_MOD_ARRIENDO = @ve_cod_mod_arriendo
		  and M.COD_MOD_ARRIENDO = I.COD_MOD_ARRIENDO
		order by I.ORDEN

		select @cod_arriendo = cod_arriendo
		from mod_arriendo
		where cod_mod_arriendo = @ve_cod_mod_arriendo

		declare C_IT_ARR CURSOR FOR  
		select cod_item_arriendo
				,cod_item_mod_arriendo
		from  item_arriendo
		where cod_arriendo = @cod_arriendo
		  and cod_producto = 'TE'

		OPEN C_IT_ARR
		FETCH C_IT_ARR INTO @vc_cod_item_arriendo, @vc_cod_item_mod_arriendo
		WHILE @@FETCH_STATUS = 0 BEGIN	
			exec sp_arr_crear_producto_TE @vc_cod_item_arriendo

			select @vl_cod_producto_TE = cod_producto
			from item_arriendo
			where cod_item_arriendo = @vc_cod_item_arriendo

			update item_mod_arriendo
			set cod_producto = @vl_cod_producto_TE
			where cod_item_mod_arriendo = @vc_cod_item_mod_arriendo

			FETCH C_IT_ARR INTO @vc_cod_item_arriendo, @vc_cod_item_mod_arriendo
		END
		CLOSE C_IT_ARR
		DEALLOCATE C_IT_ARR		
	end
END