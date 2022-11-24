CREATE PROCEDURE [dbo].[spx_carga_item_gd]
AS
BEGIN 
	insert into ITEM_GUIA_DESPACHO
           (COD_GUIA_DESPACHO
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
     select agd.cod_guia_despacho --COD_GUIA_DESPACHO
			,SECUENCIA
			,isnull(ITEM, SECUENCIA)
			,MODELO_EQUIPO
			,DESCRIPCION
			,ENTREGADO --CANTIDAD
			,PRECIO
			,null --COD_ITEM_DOC
			,'4D' --TIPO_DOC
			,null --COD_TIPO_TE
			,null --MOTIVO_TE
	from aux_guia_despacho agd, aux_item_guia_despacho aigd
	where agd.cod_guia_despacho is not null
	and agd.numero_guia_despacho = aigd.numero_guia_despacho
END
go