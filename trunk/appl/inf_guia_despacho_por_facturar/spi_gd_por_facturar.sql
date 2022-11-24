--------------- spi_gd_por_facturar --------------
ALTER PROCEDURE spi_gd_por_facturar(@ve_cod_usuario			numeric)
AS
BEGIN

declare
	@vl_fecha_actual		datetime

	set @vl_fecha_actual = getdate()
	
	-- borra el resultado de informes anteriores del mismo usuario
	delete INF_GD_POR_FACTURAR
	where cod_usuario = @ve_cod_usuario

	-- insert el resultado del informe en la tabla de informe
	declare @TEMPO TABLE     --creación de variable tipo tabla temporal
		(COD_GUIA_DESPACHO		numeric)

-- se agrega condion que si la GD esta recepcionada por GR no se debe considerar
-- ver mail de SP (enviado por MH del 8-6-2011)
	insert into @TEMPO (COD_GUIA_DESPACHO)
	select G.COD_GUIA_DESPACHO
	from GUIA_DESPACHO G
	where G.COD_ESTADO_DOC_SII in (2,3)
	  and G.COD_TIPO_GUIA_DESPACHO = 1 -- venta
	  and G.COD_GUIA_DESPACHO not in (select COD_GUIA_DESPACHO from GUIA_DESPACHO_FACTURA)
	  and G.COD_GUIA_DESPACHO not in (select COD_DOC 
										from GUIA_RECEPCION GR
										where GR.tipo_doc = 'GUIA_DESPACHO'
										  and GR.COD_ESTADO_GUIA_RECEPCION = 2)--impresa
	-- mail SP 10-08-2011, no debe considerar aquellas GD que son de NV de que tengan al menos 1 FA de exportacion
	  and G.COD_DOC not in (select cod_nota_venta
							from factura_exportacion)
							
	insert into INF_GD_POR_FACTURAR
		(FECHA_INF_GD_POR_FACTURAR  
		,COD_USUARIO                
		,NRO_GUIA_DESPACHO          
		,FECHA_GUIA_DESPACHO        
		,RUT                        
		,DIG_VERIF                  
		,NOM_EMPRESA                
		,COD_NOTA_VENTA             
		,FECHA_NOTA_VENTA           
	)
	select @vl_fecha_actual
			,@ve_cod_usuario
			,G.NRO_GUIA_DESPACHO
			,G.FECHA_GUIA_DESPACHO
			,G.RUT
			,G.DIG_VERIF
			,G.NOM_EMPRESA
			,G.COD_DOC
			,N.FECHA_NOTA_VENTA
	from @TEMPO T, GUIA_DESPACHO G, NOTA_VENTA N
	where G.COD_GUIA_DESPACHO = T.COD_GUIA_DESPACHO
	  and N.COD_NOTA_VENTA = G.COD_DOC
	  and dbo.f_nv_porc_facturado(N.COD_NOTA_VENTA) < 100
END
