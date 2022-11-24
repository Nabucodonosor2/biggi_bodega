-------------------- spu_usuario ---------------------------------
alter PROCEDURE [dbo].[spu_usuario](@ve_operacion varchar(20)
									,@ve_cod_usuario numeric
									,@ve_nom_usuario varchar(100)=NULL
									,@ve_login varchar(100)=NULL
									,@ve_password varchar(100)=NULL
									,@ve_cod_perfil numeric=NULL
									,@ve_autoriza_ingreso varchar(1)=NULL
									,@ve_mail varchar(100)=NULL
									,@ve_es_vendedor varchar(1)=NULL
									,@ve_porc_participacion T_PORCENTAJE=NULL
									,@ve_porc_modifica_precio T_PORCENTAJE=NULL
									,@ve_telefono varchar(100)=NULL
									,@ve_celular varchar(100)=NULL
									,@cod_empresa numeric = NULL
									,@ve_porc_modifica_precio_oc T_PORCENTAJE=NULL
									,@ve_acceso_libre_nv varchar(1)=NULL
									,@ve_ini_usuario varchar(4)=NULL
									,@ve_porc_descuento_permitido T_PORCENTAJE=NULL
									,@ve_vendedor_visible_filtro numeric)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into usuario (nom_usuario
							,login
							,password
							,cod_perfil
							,autoriza_ingreso
							,mail
							,es_vendedor
							,porc_participacion
							,porc_modifica_precio
							,telefono
							,celular
							,cod_empresa
							,porc_modifica_precio_oc
							,acceso_libre_nv
							,ini_usuario
							,porc_descuento_permitido
							,es_rental
							,fecha_password
							,vendedor_visible_filtro)
				values (@ve_nom_usuario
						,@ve_login
						,@ve_password
						,@ve_cod_perfil
						,@ve_autoriza_ingreso
						,@ve_mail
						,@ve_es_vendedor
						,@ve_porc_participacion
						,@ve_porc_modifica_precio
						,@ve_telefono
						,@ve_celular
						,@cod_empresa
						,@ve_porc_modifica_precio_oc
						,@ve_acceso_libre_nv
						,@ve_ini_usuario
						,@ve_porc_descuento_permitido
						,'N'
						,getdate()
						,@ve_vendedor_visible_filtro)
	end 
	if (@ve_operacion='UPDATE') begin
		-- '*--SinCambio--*' encriptado equivale a '4b197ae8c766311824f077e3885d3478'
		IF (@ve_password = '4b197ae8c766311824f077e3885d3478') 
			update usuario
			set nom_usuario				= @ve_nom_usuario, 
				login					= @ve_login, 
				cod_perfil				= @ve_cod_perfil,
				autoriza_ingreso		= @ve_autoriza_ingreso, 
				mail					= @ve_mail, 
				es_vendedor				= @ve_es_vendedor, 
				porc_participacion		= @ve_porc_participacion, 
				porc_modifica_precio	= @ve_porc_modifica_precio,
				telefono				= @ve_telefono,
				celular					= @ve_celular, 
				cod_empresa				= @cod_empresa,
				porc_modifica_precio_oc = @ve_porc_modifica_precio_oc,
				acceso_libre_nv			= @ve_acceso_libre_nv,
				ini_usuario				= @ve_ini_usuario,
				porc_descuento_permitido= @ve_porc_descuento_permitido,
				vendedor_visible_filtro = @ve_vendedor_visible_filtro
			where cod_usuario = @ve_cod_usuario
		else
			update usuario
			set nom_usuario				= @ve_nom_usuario, 
				login					= @ve_login, 
				password				= @ve_password, 
				cod_perfil				= @ve_cod_perfil,
				autoriza_ingreso		= @ve_autoriza_ingreso, 
				mail					= @ve_mail, 
				es_vendedor				= @ve_es_vendedor, 
				porc_participacion		= @ve_porc_participacion, 
				porc_modifica_precio	= @ve_porc_modifica_precio,
				telefono				= @ve_telefono,
				celular					= @ve_celular, 
				cod_empresa				= @cod_empresa,
				porc_modifica_precio_oc	= @ve_porc_modifica_precio_oc,
				acceso_libre_nv			= @ve_acceso_libre_nv,
				ini_usuario				= @ve_ini_usuario,
				porc_descuento_permitido = @ve_porc_descuento_permitido,
				vendedor_visible_filtro = @ve_vendedor_visible_filtro
			where cod_usuario = @ve_cod_usuario
		end
	else if (@ve_operacion='DELETE') begin
		delete usuario
    	where cod_usuario = @ve_cod_usuario
	end
END
go