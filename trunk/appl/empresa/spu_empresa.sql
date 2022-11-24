-------------------- spu_empresa ---------------------------------
CREATE PROCEDURE [dbo].[spu_empresa](@ve_operacion varchar(30)
							,@ve_cod_empresa numeric
							,@ve_rut numeric
							,@ve_dig_verif varchar(1)
							,@ve_alias varchar(30)
							,@ve_nom_empresa varchar(100)
							,@ve_giro varchar(100)
							,@ve_cod_clasif_empresa numeric
							,@ve_direccion_internet varchar(30)
							,@ve_rut_representante numeric
							,@ve_dig_verif_representante varchar(1)
							,@ve_nom_representante varchar(100)
							,@ve_es_cliente varchar(1)
							,@ve_es_proveedor_interno varchar(1)
							,@ve_es_proveedor_externo varchar(1)
							,@ve_es_personal varchar(1)
							,@ve_imprimir_emp_mas_suc varchar(1)
							,@ve_sujeto_a_aprobacion varchar(1)
							,@ve_porc_dscto_corporativo T_PORCENTAJE
							,@ve_cod_usuario numeric
							,@ve_tipo_participacion varchar(4))
AS
BEGIN
Declare @ve_porc numeric
Declare @existe_hoy numeric
Declare @cod_empresa_creada numeric

	if (@ve_operacion='INSERT') 
		begin
			insert into empresa (rut, dig_verif, alias, nom_empresa, giro, cod_clasif_empresa, direccion_internet, rut_representante, dig_verif_representante, nom_representante, es_cliente, es_proveedor_interno, es_proveedor_externo, es_personal, imprimir_emp_mas_suc, sujeto_a_aprobacion, cod_usuario, tipo_participacion)
			values (@ve_rut, @ve_dig_verif, @ve_alias, @ve_nom_empresa, @ve_giro, @ve_cod_clasif_empresa, @ve_direccion_internet, @ve_rut_representante, @ve_dig_verif_representante, @ve_nom_representante, @ve_es_cliente, @ve_es_proveedor_interno, @ve_es_proveedor_externo, @ve_es_personal, @ve_imprimir_emp_mas_suc, @ve_sujeto_a_aprobacion, @ve_cod_usuario, @ve_tipo_participacion)
		end
	else if (@ve_operacion='DSCTO_CORPORATIVO_EMPRESA')
		begin
			-- solo si se ingreso un descuento corporativo lo graba en la tabla DSCTO_CORPORATIVO_EMPRESA
			IF (@ve_porc_dscto_corporativo <> 0)
			BEGIN
				insert into DSCTO_CORPORATIVO_EMPRESA 
						   (cod_empresa, porc_dscto_corporativo, fecha_inicio_vigencia)
				values 
						   (@ve_cod_empresa, 
							@ve_porc_dscto_corporativo, 
							getdate())
			END
		end
	else if (@ve_operacion='UPDATE') 
		begin
			update empresa 
			set rut = @ve_rut, 
				dig_verif = @ve_dig_verif, 
				alias = @ve_alias, 
				nom_empresa = @ve_nom_empresa, 
				giro = @ve_giro, 
				cod_clasif_empresa = @ve_cod_clasif_empresa, 
				direccion_internet = @ve_direccion_internet,
				rut_representante = @ve_rut_representante, 
				dig_verif_representante = @ve_dig_verif_representante, 
				nom_representante = @ve_nom_representante, 
				es_cliente = @ve_es_cliente, 
				es_proveedor_interno = @ve_es_proveedor_interno, 
				es_proveedor_externo = @ve_es_proveedor_externo, 
				es_personal = @ve_es_personal, 
				imprimir_emp_mas_suc = @ve_imprimir_emp_mas_suc, 
				sujeto_a_aprobacion = @ve_sujeto_a_aprobacion,
				cod_usuario = @ve_cod_usuario,
				-- porc_dscto_corporativo = @ve_porc_dscto_corporativo
				tipo_participacion = @ve_tipo_participacion
		    where cod_empresa = @ve_cod_empresa
		
			select @ve_porc = (dbo.f_get_porc_dscto_corporativo_empresa(@ve_cod_empresa, dbo.f_makedate(day(getdate()),month(getdate()),year(getdate())))) 
			IF (@ve_porc_dscto_corporativo <> @ve_porc)
			BEGIN
				select @existe_hoy = count(*)
				from dscto_corporativo_empresa
				where cod_empresa = @ve_cod_empresa 
					  and fecha_inicio_vigencia = dbo.f_makedate(day(getdate()),month(getdate()),year(getdate()))
				
				IF (@existe_hoy > 0)
				BEGIN
						update DSCTO_CORPORATIVO_EMPRESA
						set porc_dscto_corporativo = @ve_porc_dscto_corporativo
						where cod_empresa = @ve_cod_empresa and fecha_inicio_vigencia = dbo.f_makedate(day(getdate()),month(getdate()),year(getdate()))
				END
				ELSE
				BEGIN
					insert into DSCTO_CORPORATIVO_EMPRESA 
							    (cod_empresa, porc_dscto_corporativo, fecha_inicio_vigencia)
					values 
								(@ve_cod_empresa, 
								 @ve_porc_dscto_corporativo, 
								 dbo.f_makedate(day(getdate()),month(getdate()),year(getdate())))
				END
			END
		end 
END
go