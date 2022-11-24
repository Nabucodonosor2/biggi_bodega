--------------------------spr_nv_marca---------------------------
-- creado por Cristian Sánchez el 20/04/09
-- este procedimiento permite listar todas las marca de despachos que existan por item
-- se genera por la cantidad de productos que esten en cada item
CREATE PROCEDURE [dbo].[spr_nv_marca](@ve_cod_nota_venta numeric, @ve_item varchar(4000))
AS
BEGIN
	-- @ve_item contiene cod_item1|cantidad1|..|cod_itemN|cantidadN|
	declare	@pos int,
			@cod_item_nv varchar(2000),
			@cant_item_nv int

	declare @TEMPO TABLE 	 
				(cod_nota_venta numeric not null,
				referencia  varchar (100) not null,
				nom_empresa varchar(100) not null,
				nom_persona varchar(100) not null,
				dir_despacho varchar (100) null,
				comuna_ciudad varchar (100)null,
				fono_fax varchar(50)null,
				obs_despacho text null,
				nro_orden_compra varchar(20) null,
				nom_estado_nota_venta varchar(20) null,
				item varchar(10) not null,
				cod_producto varchar(30) not null,
				nom_producto varchar(100) not null,
				cantidad numeric(10,2),
				nom_empresa_emisor varchar(100) not null,
				dir_empresa varchar(100) not null,
				tel_empresa varchar(100) not null,
				fax_empresa varchar(100) not null,
				mail_empresa varchar(100) not null,
				ciudad_empresa varchar(100) not null,
				pais_empresa varchar(100) not null)  


while (@ve_item<>'')
begin		
			set @pos = CHARINDEX('|', @ve_item) 
			set @cod_item_nv = substring(@ve_item, 1, @pos - 1) 
			set @ve_item = substring(@ve_item, @pos + 1, len(@ve_item) - @pos)
		
			set @pos = CHARINDEX('|', @ve_item) 
			set @cant_item_nv = convert(int, substring(@ve_item, 1, @pos - 1))
			set @ve_item = substring(@ve_item, @pos + 1, len(@ve_item) - @pos)
			
			while (@cant_item_nv<>0)
			begin

				insert into @TEMPO
				SELECT NV.COD_NOTA_VENTA,
						NV.REFERENCIA,
						E.NOM_EMPRESA,
						P.NOM_PERSONA,	
						dbo.f_get_direccion('SUCURSAL', NV.COD_SUCURSAL_DESPACHO, '[DIRECCION]')DIR_DESPACHO,	
						dbo.f_get_direccion('SUCURSAL', NV.COD_SUCURSAL_DESPACHO, 'COMUNA: [NOM_COMUNA] - CIUDAD:[NOM_CIUDAD]')COMUNA_CIUDAD,
						dbo.f_get_direccion('SUCURSAL', NV.COD_SUCURSAL_DESPACHO, 'FONO: [TELEFONO] - FAX:[FAX]')FONO_FAX,
					 	NV.OBS_DESPACHO,	
						NV.NRO_ORDEN_COMPRA,
						ENV.NOM_ESTADO_NOTA_VENTA,
						INV.ITEM,
						INV.COD_PRODUCTO,
						INV.NOM_PRODUCTO,
						INV.CANTIDAD,
						dbo.f_get_parametro(6) NOM_EMPRESA_EMISOR,
						dbo.f_get_parametro(10) DIR_EMPRESA,
						dbo.f_get_parametro(11) TEL_EMPRESA,
						dbo.f_get_parametro(12) FAX_EMPRESA,
						dbo.f_get_parametro(13) MAIL_EMPRESA,
						dbo.f_get_parametro(14) CIUDAD_EMPRESA,
						dbo.f_get_parametro(15) PAIS_EMPRESA
				FROM  NOTA_VENTA NV, EMPRESA E, SUCURSAL SD,
						PERSONA P ,ITEM_NOTA_VENTA INV, ESTADO_NOTA_VENTA ENV 
														 
				WHERE   NV.COD_NOTA_VENTA = @ve_cod_nota_venta AND
						INV.COD_ITEM_NOTA_VENTA = @cod_item_nv AND
						E.COD_EMPRESA = NV.COD_EMPRESA AND
						SD.COD_SUCURSAL = NV.COD_SUCURSAL_DESPACHO AND
						P.COD_PERSONA = NV.COD_PERSONA AND
						INV.COD_NOTA_VENTA = NV.COD_NOTA_VENTA AND
						NV.COD_ESTADO_NOTA_VENTA = ENV.COD_ESTADO_NOTA_VENTA

		     		set  @cant_item_nv = @cant_item_nv - 1
			end	
end
	
	SELECT COD_NOTA_VENTA,
			REFERENCIA,
			NOM_EMPRESA,
			NOM_PERSONA,	
			DIR_DESPACHO,
			COMUNA_CIUDAD,	
			FONO_FAX,		
			OBS_DESPACHO,	
			NRO_ORDEN_COMPRA,
			NOM_ESTADO_NOTA_VENTA,
			ITEM,
			COD_PRODUCTO,
			NOM_PRODUCTO,
			CANTIDAD,
			NOM_EMPRESA_EMISOR,
			DIR_EMPRESA,
			TEL_EMPRESA,
			FAX_EMPRESA,
			MAIL_EMPRESA,
			CIUDAD_EMPRESA,
			PAIS_EMPRESA
		from @TEMPO
END
go