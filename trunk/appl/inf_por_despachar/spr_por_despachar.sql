---------------------- spr_por_despachar ------------------------
ALTER PROCEDURE [dbo].[spr_por_despachar](@ve_cod_usuario numeric)
AS
/*
Busca los equipos por despachar del usuario @ve_cod_usuario
@ve_cod_usuario: 0 indica todos
*/
BEGIN
	-- declara tabla temporal
	declare @TEMPO TABLE 	 
		   (cod_nota_venta			numeric
			,fecha_nota_venta		varchar(20)	
			,nom_empresa			varchar(100)
			,cod_usuario_vendedor1	numeric		
			,nom_usuario			varchar(100)
			,ini_usuario			varchar(100)
			,item					varchar(10)	
			,nom_producto			varchar(100)
			,cod_producto			varchar (30)
			,cantidad				numeric (10,2)
			,cantidad_por_despachar numeric (10,2)
			,fecha_entrega			varchar(20)
			,dias_atraso			numeric
			,es_compuesto			varchar(1)
			,fecha					varchar(30)
			,nom_empresa_emisor		varchar(100)
			,dir_empresa			varchar(100) 
			,tel_empresa			varchar(100) 
			,fax_empresa			varchar(100) 
			,mail_empresa			varchar(100) 
			,ciudad_empresa			varchar(100) 
			,pais_empresa			varchar(100) 
			,sitio_web_empresa		varchar(100)
			,rut_empresa			varchar(100))		

	-- declara variables
	declare  @COD_NOTA_VENTA			numeric	
			,@FECHA_NOTA_VENTA			varchar(20)	
			,@NOM_EMPRESA				varchar(100)
			,@COD_USUARIO_VENDEDOR1		numeric
			,@INI_USUARIO				varchar(100)
			,@NOM_USUARIO				varchar(100)
			,@ITEM						varchar(10)	
			,@NOM_PRODUCTO				varchar(100)
			,@COD_PRODUCTO				varchar (30)
			,@CANTIDAD					T_CANTIDAD 
			,@CANTIDAD_POR_DESPACHAR	T_CANTIDAD 
			,@FECHA_ENTREGA				varchar(20)
			,@DIAS_ATRASO				numeric
			,@ES_COMPUESTO				varchar(1)
			,@FECHA						varchar(30)
			,@vl_CANTIDAD				T_CANTIDAD 
			,@vl_COD_PRODUCTO_HIJO		varchar(30)
			,@vl_NOM_PRODUCTO			varchar(100)
			,@vl_ES_VENDEDOR			T_SI_NO
			
		--aqui van los datos dl footer en el informe
			,@NOM_EMPRESA_EMISOR		varchar(100)
			,@DIR_EMPRESA				varchar(100)
			,@TEL_EMPRESA				varchar(100)
			,@FAX_EMPRESA				varchar(100)
			,@MAIL_EMPRESA				varchar(100)
			,@CIUDAD_EMPRESA			varchar(100)
			,@PAIS_EMPRESA				varchar(100)
			,@SITIO_WEB_EMPRESA			varchar(100)
			,@RUT_EMPRESA				varchar(100)
	
	-- declara cursor
	declare C_CURSOR cursor for
	select NV.COD_NOTA_VENTA
			,convert(varchar(20), NV.FECHA_NOTA_VENTA, 3) FECHA_NOTA_VENTA
			,E.NOM_EMPRESA
			,NV.COD_USUARIO_VENDEDOR1
			,U.NOM_USUARIO
			,U.INI_USUARIO
			,INV.ITEM
			,INV.COD_PRODUCTO
			,INV.NOM_PRODUCTO
			,INV.CANTIDAD
			,dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default) CANTIDAD_POR_DESPACHAR
			,convert(varchar(20), NV.FECHA_ENTREGA, 3) FECHA_ENTREGA
			,case 
				when ((DATEDIFF ( day , NV.FECHA_ENTREGA , getdate())) < 0) then 0 
				else DATEDIFF ( day , NV.FECHA_ENTREGA , getdate())
			end  DIAS_ATRASO
			,P.ES_COMPUESTO
			,dbo.f_format_date(getdate(), 3) FECHA
			,dbo.f_get_parametro(6) NOM_EMPRESA_EMISOR
			,dbo.f_get_parametro(10) DIR_EMPRESA
			,dbo.f_get_parametro(11) TEL_EMPRESA
			,dbo.f_get_parametro(12) FAX_EMPRESA
			,dbo.f_get_parametro(13) MAIL_EMPRESA
			,dbo.f_get_parametro(14) CIUDAD_EMPRESA
			,dbo.f_get_parametro(15) PAIS_EMPRESA
			,dbo.f_get_parametro(25) SITIO_WEB_EMPRESA
			,dbo.f_get_parametro(20) RUT_EMPRESA 
	from	NOTA_VENTA NV, ITEM_NOTA_VENTA INV, PRODUCTO P, EMPRESA E, USUARIO U
	where	NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA AND
			P.COD_PRODUCTO = INV.COD_PRODUCTO AND
			E.COD_EMPRESA = NV.COD_EMPRESA  AND
			NV.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND
			dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default) > 0 and
			NV.cod_estado_nota_venta <> 3	-- Anulada
	
	-- abre cursor
	OPEN C_CURSOR
	FETCH C_CURSOR INTO 
			 @COD_NOTA_VENTA
			,@FECHA_NOTA_VENTA
			,@NOM_EMPRESA
			,@COD_USUARIO_VENDEDOR1
			,@NOM_USUARIO
			,@INI_USUARIO
			,@ITEM
			,@COD_PRODUCTO
			,@NOM_PRODUCTO
			,@CANTIDAD
			,@CANTIDAD_POR_DESPACHAR
			,@FECHA_ENTREGA
			,@DIAS_ATRASO
			,@ES_COMPUESTO
			,@FECHA
			,@NOM_EMPRESA_EMISOR		
			,@DIR_EMPRESA				
			,@TEL_EMPRESA				
			,@FAX_EMPRESA				
			,@MAIL_EMPRESA				
			,@CIUDAD_EMPRESA			
			,@PAIS_EMPRESA				
			,@SITIO_WEB_EMPRESA			
			,@RUT_EMPRESA								

	-- recorre cursor		
	WHILE @@FETCH_STATUS = 0
	BEGIN
		
		insert into @TEMPO
				(COD_NOTA_VENTA			
				,FECHA_NOTA_VENTA		
				,NOM_EMPRESA			
				,COD_USUARIO_VENDEDOR1	
				,NOM_USUARIO
				,INI_USUARIO
				,ITEM		
				,COD_PRODUCTO
				,NOM_PRODUCTO
				,CANTIDAD
				,CANTIDAD_POR_DESPACHAR
				,FECHA_ENTREGA
				,DIAS_ATRASO
				,ES_COMPUESTO
				,FECHA
				,NOM_EMPRESA_EMISOR		
				,DIR_EMPRESA			
				,TEL_EMPRESA			
				,FAX_EMPRESA			
				,MAIL_EMPRESA			
				,CIUDAD_EMPRESA			
				,PAIS_EMPRESA			
				,SITIO_WEB_EMPRESA		
				,RUT_EMPRESA)		

			values
				(@COD_NOTA_VENTA
				,@FECHA_NOTA_VENTA
				,@NOM_EMPRESA
				,@COD_USUARIO_VENDEDOR1
				,@NOM_USUARIO
				,@INI_USUARIO
				,@ITEM
				,@COD_PRODUCTO
				,@NOM_PRODUCTO
				,@CANTIDAD
				,@CANTIDAD_POR_DESPACHAR
				,@FECHA_ENTREGA
				,@DIAS_ATRASO
				,@ES_COMPUESTO
				,@FECHA
				,@NOM_EMPRESA_EMISOR		
				,@DIR_EMPRESA				
				,@TEL_EMPRESA				
				,@FAX_EMPRESA				
				,@MAIL_EMPRESA				
				,@CIUDAD_EMPRESA			
				,@PAIS_EMPRESA				
				,@SITIO_WEB_EMPRESA			
				,@RUT_EMPRESA)
			
		-- debe hacer insert de los compuestos
		if (@ES_COMPUESTO = 'S')
		begin
			select 	@vl_CANTIDAD = CANTIDAD
					,@vl_COD_PRODUCTO_HIJO = COD_PRODUCTO_HIJO
					,@vl_NOM_PRODUCTO = P.NOM_PRODUCTO 
			from PRODUCTO_COMPUESTO PC, 
				 PRODUCTO P
			where PC.COD_PRODUCTO = @COD_PRODUCTO and 
				  COD_PRODUCTO_HIJO = P.COD_PRODUCTO
			
			insert into @TEMPO
				(cod_nota_venta			
				,fecha_nota_venta		
				,nom_empresa			
				,cod_usuario_vendedor1
				,nom_usuario	
				,ini_usuario	
				,item		
				,cod_producto
				,nom_producto
				,cantidad
				,cantidad_por_despachar
				,fecha_entrega
				,dias_atraso
				,es_compuesto
				,fecha
				,nom_empresa_emisor		
				,dir_empresa			
				,tel_empresa			
				,fax_empresa			
				,mail_empresa			
				,ciudad_empresa			
				,pais_empresa			
				,sitio_web_empresa		
				,rut_empresa)		


			values
				(@COD_NOTA_VENTA
				,''
				,''
				,@COD_USUARIO_VENDEDOR1
				,@NOM_USUARIO
				,''
				,@ITEM
				,@vl_COD_PRODUCTO_HIJO
				,'***'+@vl_NOM_PRODUCTO
				,@CANTIDAD * @vl_CANTIDAD
				,@CANTIDAD_POR_DESPACHAR * @vl_CANTIDAD
				,''
				,NULL
				,'H'
				,@FECHA
				,@NOM_EMPRESA_EMISOR		
				,@DIR_EMPRESA				
				,@TEL_EMPRESA				
				,@FAX_EMPRESA				
				,@MAIL_EMPRESA				
				,@CIUDAD_EMPRESA			
				,@PAIS_EMPRESA				
				,@SITIO_WEB_EMPRESA			
				,@RUT_EMPRESA)
		end


		FETCH C_CURSOR INTO 
			 @COD_NOTA_VENTA
			,@FECHA_NOTA_VENTA
			,@NOM_EMPRESA
			,@COD_USUARIO_VENDEDOR1
			,@NOM_USUARIO
			,@INI_USUARIO
			,@ITEM
			,@COD_PRODUCTO
			,@NOM_PRODUCTO
			,@CANTIDAD
			,@CANTIDAD_POR_DESPACHAR
			,@FECHA_ENTREGA
			,@DIAS_ATRASO
			,@ES_COMPUESTO
			,@FECHA
			,@NOM_EMPRESA_EMISOR		
			,@DIR_EMPRESA				
			,@TEL_EMPRESA				
			,@FAX_EMPRESA				
			,@MAIL_EMPRESA				
			,@CIUDAD_EMPRESA			
			,@PAIS_EMPRESA				
			,@SITIO_WEB_EMPRESA			
			,@RUT_EMPRESA
			
	END
	CLOSE C_CURSOR
	DEALLOCATE C_CURSOR
	
	if (@ve_cod_usuario=0)
		select COD_NOTA_VENTA
				,FECHA_NOTA_VENTA
				,NOM_EMPRESA
				,COD_USUARIO_VENDEDOR1
				,U.NOM_USUARIO
				,T.INI_USUARIO
				,ITEM
				,COD_PRODUCTO
				,NOM_PRODUCTO
				,CANTIDAD
				,CANTIDAD_POR_DESPACHAR
				,FECHA_ENTREGA
				,DIAS_ATRASO
				,ES_COMPUESTO	
				,FECHA
				,NOM_EMPRESA_EMISOR		
				,DIR_EMPRESA				
				,TEL_EMPRESA				
				,FAX_EMPRESA				
				,MAIL_EMPRESA				
				,CIUDAD_EMPRESA			
				,PAIS_EMPRESA				
				,SITIO_WEB_EMPRESA			
				,RUT_EMPRESA
		from @TEMPO T,USUARIO U
		where U.COD_USUARIO = COD_USUARIO_VENDEDOR1
		  and CANTIDAD_POR_DESPACHAR > 0
		order by COD_NOTA_VENTA asc, item asc, es_compuesto desc
	else
		select COD_NOTA_VENTA
				,FECHA_NOTA_VENTA
				,NOM_EMPRESA
				,COD_USUARIO_VENDEDOR1
				,U.NOM_USUARIO
				,T.INI_USUARIO
				,ITEM
				,COD_PRODUCTO
				,NOM_PRODUCTO
				,CANTIDAD
				,CANTIDAD_POR_DESPACHAR
				,FECHA_ENTREGA
				,DIAS_ATRASO
				,ES_COMPUESTO	
				,FECHA
				,NOM_EMPRESA_EMISOR		
				,DIR_EMPRESA				
				,TEL_EMPRESA				
				,FAX_EMPRESA				
				,MAIL_EMPRESA				
				,CIUDAD_EMPRESA			
				,PAIS_EMPRESA				
				,SITIO_WEB_EMPRESA			
				,RUT_EMPRESA
		from @TEMPO T,USUARIO U
		where COD_USUARIO_VENDEDOR1 = @ve_cod_usuario
		  and U.COD_USUARIO = COD_USUARIO_VENDEDOR1
		  and CANTIDAD_POR_DESPACHAR > 0
		order by U.INI_USUARIO asc, COD_NOTA_VENTA asc, item asc, es_compuesto desc
END