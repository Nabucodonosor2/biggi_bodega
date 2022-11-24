CREATE PROCEDURE [dbo].[spx_carga_item_fa]
AS
BEGIN 
	insert into ITEM_FACTURA
           (COD_FACTURA
           ,ORDEN
           ,ITEM
           ,COD_PRODUCTO
           ,NOM_PRODUCTO
           ,CANTIDAD
           ,PRECIO
           ,COD_ITEM_DOC
           ,TIPO_DOC
           ,COD_TIPO_TE
           ,MOTIVO_TE)
     select af.cod_factura --COD_FACTURA
			,SECUENCIA
			,isnull(ITEM, SECUENCIA)
			,MODELO_EQUIPO
			,DESCRIPCION
			,CANTIDAD
			,PRECIO

			,null --COD_ITEM_DOC
			,'4D' --TIPO_DOC
			,null --COD_TIPO_TE
			,null --MOTIVO_TE
	from aux_factura af, aux_item_factura aif
	where af.cod_factura is not null
	and af.numero_factura = aif.numero_fact
END
go