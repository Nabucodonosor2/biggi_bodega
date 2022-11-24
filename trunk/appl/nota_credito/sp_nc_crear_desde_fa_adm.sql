CREATE PROCEDURE sp_nc_crear_desde_fa_adm
(
	@ve_cod_factura numeric, 
	@ve_cod_usuario numeric
)
AS
BEGIN  
	declare 
		@vl_cod_nc		numeric

	execute sp_nc_crear @ve_cod_factura, @ve_cod_usuario	
	set @vl_cod_nc = @@identity
		
	insert into item_nota_credito (
		COD_NOTA_CREDITO, 
		ORDEN, 
		ITEM, 
		COD_PRODUCTO, 
		NOM_PRODUCTO, 
		CANTIDAD, 
		PRECIO, 
		COD_ITEM_DOC,
		TIPO_DOC
	)
	values(
		@vl_cod_nc,
		10,
		'1',
		'TE',
		'__ADMINISTRATIVA__',
		1,
		0,
		null,
		null) 

	execute spu_nota_credito 'RECALCULA', @vl_cod_nc
END
