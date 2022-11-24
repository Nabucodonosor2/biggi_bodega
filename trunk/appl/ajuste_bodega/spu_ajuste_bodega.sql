-------------------- spu_ajuste_bodega ---------------------------------
alter PROCEDURE [dbo].[spu_ajuste_bodega]
(@ve_operacion varchar(20)
,@ve_cod_ajuste_bodega	numeric = null
,@ve_cod_usuario		numeric = null
,@ve_cod_bodega			numeric = null
,@ve_obs				varchar(100) = null)
AS
BEGIN	
	declare	@kl_cod_estado_emitida		numeric,
			@kl_cod_estado_confirmado	numeric,
			@kl_cod_estado_anulada		numeric,
			@vl_cod_usuario_anula		numeric

	set @kl_cod_estado_emitida		= 1  --- estado ajuste bodega
	set @kl_cod_estado_confirmado	= 2  --- estado ajuste bodega
	set @kl_cod_estado_anulada		= 3  --- estado ajuste bodega		

	if (@ve_operacion='UPDATE')
	begin
		UPDATE	ajuste_bodega		
		SET		cod_usuario			=	@ve_cod_usuario
				,cod_bodega			=	@ve_cod_bodega
				,obs				=	@ve_obs	
		WHERE	cod_ajuste_bodega	= @ve_cod_ajuste_bodega
	end
	if (@ve_operacion='INSERT') 
	begin
		insert into ajuste_bodega
					(fecha_ajuste_bodega
					,cod_usuario
					,cod_bodega
					,obs)
				values 
					(getdate()
					,@ve_cod_usuario
					,@ve_cod_bodega
					,@ve_obs)
	end
	else if (@ve_operacion='DELETE') 
	begin
		delete item_ajuste_bodega
		where cod_ajuste_bodega	= @ve_cod_ajuste_bodega

		delete ajuste_bodega
		where cod_ajuste_bodega	= @ve_cod_ajuste_bodega
	end
END
go