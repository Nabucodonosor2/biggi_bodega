-------------------- spdw_gr_load_item  ---------------------------------
alter PROCEDURE spdw_gr_load_item(@ve_tipo_doc		varchar(20)
									,@ve_cod_doc	numeric)
AS
BEGIN
	declare @TEMPO TABLE     --creación de variable tipo tabla temporal
	   (COD_ITEM_DOC					numeric
		,COD_ITEM_GUIA_RECEPCION		numeric
		,COD_GUIA_RECEPCION				numeric
		,COD_PRODUCTO					varchar(30)
		,NOM_PRODUCTO					varchar(100)
		,CANTIDAD						numeric(10,2)
		,COD_DOC						numeric
		,COD_TIPO_GUIA_RECEPCION		numeric
		,POR_RECEPCIONAR				numeric(10,2)
		,POR_RECEPCIONAR_H				numeric(10,2)
		,TD_DISPLAY_ELIMINAR			varchar(100)
		,TIPO_DOC_GR					varchar(100)
		)

	if (@ve_tipo_doc = 'FACTURA') 
		insert into @TEMPO
		SELECT ITF.COD_ITEM_FACTURA COD_ITEM_DOC
				,0 COD_ITEM_GUIA_RECEPCION
				,0 COD_GUIA_RECEPCION
				,ITF.COD_PRODUCTO
				,ITF.NOM_PRODUCTO
				,0 CANTIDAD
				,0 COD_DOC
				,0 COD_TIPO_GUIA_RECEPCION
				,dbo.f_gr_fa_cant_por_recep(COD_ITEM_FACTURA)POR_RECEPCIONAR
				,dbo.f_gr_fa_cant_por_recep(COD_ITEM_FACTURA)POR_RECEPCIONAR_H	
				,'none' TD_DISPLAY_ELIMINAR
				,'' TIPO_DOC_GR
		FROM    ITEM_FACTURA ITF, FACTURA F
		WHERE   ITF.COD_FACTURA = @ve_cod_doc AND
				ITF.COD_FACTURA = F.COD_FACTURA AND
				dbo.f_gr_fa_cant_por_recep(COD_ITEM_FACTURA) > 0
				order by ITF.COD_ITEM_FACTURA asc
	else if (@ve_tipo_doc = 'GUIA_DESPACHO')
		insert into @TEMPO
		SELECT IGD.COD_ITEM_GUIA_DESPACHO COD_ITEM_DOC
				,0 COD_ITEM_GUIA_RECEPCION
				,0 COD_GUIA_RECEPCION
				,IGD.COD_PRODUCTO
				,IGD.NOM_PRODUCTO
				,0 CANTIDAD
				,0 COD_DOC
				,0 COD_TIPO_GUIA_RECEPCION
				,dbo.f_gr_gd_cant_por_recep(COD_ITEM_GUIA_DESPACHO)POR_RECEPCIONAR
				,dbo.f_gr_gd_cant_por_recep(COD_ITEM_GUIA_DESPACHO)POR_RECEPCIONAR_H
				,'none' TD_DISPLAY_ELIMINAR
				,'' TIPO_DOC_GR
		FROM    ITEM_GUIA_DESPACHO IGD, GUIA_DESPACHO GD
		WHERE   IGD.COD_GUIA_DESPACHO = @ve_cod_doc AND
				GD.COD_GUIA_DESPACHO  = IGD.COD_GUIA_DESPACHO AND
				dbo.f_gr_gd_cant_por_recep(COD_ITEM_GUIA_DESPACHO) > 0
				order by IGD.COD_ITEM_GUIA_DESPACHO asc
	else if (@ve_tipo_doc = 'ARRIENDO') begin
		insert into @TEMPO
		SELECT I.COD_ITEM_ARRIENDO COD_ITEM_DOC
				,0 COD_ITEM_GUIA_RECEPCION
				,0 COD_GUIA_RECEPCION
				,I.COD_PRODUCTO
				,I.NOM_PRODUCTO
				,0 CANTIDAD
				,0 COD_DOC
				,0 COD_TIPO_GUIA_RECEPCION
				,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) POR_RECEPCIONAR
				,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) POR_RECEPCIONAR_H
				,'none' TD_DISPLAY_ELIMINAR
				,'' TIPO_DOC_GR
		FROM    ITEM_ARRIENDO I, ARRIENDO A
		WHERE   A.COD_ARRIENDO = @ve_cod_doc AND
				A.COD_ARRIENDO = I.COD_ARRIENDO AND
			  	dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) > 0

		-- se deben agrupar los items con el mismo COD_PRODUCTO,
		-- para los agrupados se manteiene el primer COD_ITEM_ARRIENDO
		DECLARE C_TEMPO CURSOR FOR  
		SELECT COD_ITEM_DOC 
				,COD_PRODUCTO
		from @TEMPO
		order by COD_PRODUCTO

		declare
			@vl_cod_item_doc		numeric
			,@vl_cod_producto		varchar(100)
			,@vl_cod_producto_ant	varchar(100)
			,@vl_cod_item_doc_ant	numeric

		set @vl_cod_producto_ant = ''
		set @vl_cod_item_doc_ant = 0
		OPEN C_TEMPO
		FETCH C_TEMPO INTO @vl_cod_item_doc, @vl_cod_producto
		WHILE @@FETCH_STATUS = 0 BEGIN	
			if (@vl_cod_producto_ant = @vl_cod_producto) begin
				-- deja en cero la catidad por recepcionar
				-- NO es necesario sumar las cantidades, porque en cada linea esta siempre el total por recpcionar
				update @TEMPO
				set POR_RECEPCIONAR = 0
					,POR_RECEPCIONAR_H = 0
				where COD_ITEM_DOC = @vl_cod_item_doc
			end 
			else begin
				set @vl_cod_producto_ant = @vl_cod_producto
				set @vl_cod_item_doc_ant = @vl_cod_item_doc
			end
			FETCH C_TEMPO INTO @vl_cod_item_doc, @vl_cod_producto
		END
		CLOSE C_TEMPO
		DEALLOCATE C_TEMPO
		
		delete @TEMPO
		where POR_RECEPCIONAR = 0
	end

	select * from @TEMPO

END
